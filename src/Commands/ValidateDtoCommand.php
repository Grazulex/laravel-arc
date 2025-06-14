<?php

namespace Grazulex\Arc\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function is_array;

class ValidateDtoCommand extends Command
{
    protected $signature = 'dto:validate {class : The DTO class to validate} {--data= : JSON data to validate} {--file= : File containing JSON data}';

    protected $description = 'Validate data against DTO validation rules';

    public function handle(): int
    {
        $className = $this->argument('class');
        $jsonData = $this->option('data');
        $file = $this->option('file');

        try {
            // Get data to validate
            $data = $this->getData($jsonData, $file);

            // Resolve and instantiate DTO class
            $fullClassName = $this->resolveClassName($className);

            if (!class_exists($fullClassName)) {
                $this->error("Class {$fullClassName} does not exist");

                return self::FAILURE;
            }

            // Attempt to create DTO instance and validate
            $result = $this->validateData($fullClassName, $data);

            if ($result['success']) {
                $this->info('✅ Validation passed!');
                $this->displayValidationResults($result);

                return self::SUCCESS;
            }
            $this->error('❌ Validation failed!');
            $this->displayValidationResults($result);

            return self::FAILURE;
        } catch (Exception $e) {
            $this->error("Error validating DTO: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Get data from various sources.
     *
     * @return array<string, mixed>
     */
    private function getData(?string $jsonData, ?string $file): array
    {
        if ($file) {
            if (!file_exists($file)) {
                throw new Exception("File {$file} does not exist");
            }
            $content = file_get_contents($file);
            if ($content === false) {
                throw new Exception("Could not read file {$file}");
            }
            $data = json_decode($content, true);
        } elseif ($jsonData) {
            $data = json_decode($jsonData, true);
        } else {
            // Interactive mode
            $this->info('Enter JSON data (end with empty line):');
            $input = '';
            while (true) {
                $line = $this->ask('> ');
                if (empty($line)) {
                    break;
                }
                $input .= $line . "\n";
            }
            $data = json_decode($input, true);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data: ' . json_last_error_msg());
        }

        return $data ?? [];
    }

    /**
     * Validate data against DTO.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function validateData(string $className, array $data): array
    {
        $result = [
            'success' => false,
            'dto_created' => false,
            'validation_errors' => [],
            'creation_error' => null,
            'dto_data' => null,
            'validation_rules' => [],
        ];

        try {
            // Try to create DTO instance
            $dto = new $className($data);
            $result['dto_created'] = true;
            $result['dto_data'] = $dto->toArray();

            // Get validation rules if DTO supports it
            if (method_exists($dto, 'rules')) {
                $rules = $dto->rules();
                $result['validation_rules'] = $rules;

                // Validate using Laravel's validator
                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    $result['validation_errors'] = $validator->errors()->toArray();
                } else {
                    $result['success'] = true;
                }
            } else {
                // No explicit validation rules, consider successful creation as valid
                $result['success'] = true;
            }
        } catch (Exception $e) {
            $result['creation_error'] = $e->getMessage();
            $result['success'] = false;
        }

        return $result;
    }

    /**
     * Display validation results.
     *
     * @param array<string, mixed> $result
     */
    private function displayValidationResults(array $result): void
    {
        $this->line('');

        // DTO Creation Status
        $this->info('📦 DTO Creation:');
        if ($result['dto_created']) {
            $this->line('✅ DTO instance created successfully');
        } else {
            $this->line('❌ Failed to create DTO instance');
            if ($result['creation_error']) {
                $this->error("Error: {$result['creation_error']}");
            }
        }

        $this->line('');

        // Validation Rules
        if (!empty($result['validation_rules'])) {
            $this->info('📋 Validation Rules:');
            foreach ($result['validation_rules'] as $field => $rules) {
                $this->line("  {$field}: " . (is_array($rules) ? implode('|', $rules) : $rules));
            }
            $this->line('');
        }

        // Validation Errors
        if (!empty($result['validation_errors'])) {
            $this->error('❌ Validation Errors:');
            foreach ($result['validation_errors'] as $field => $errors) {
                $this->line("  {$field}:");
                foreach ((array) $errors as $error) {
                    $this->line("    - {$error}");
                }
            }
            $this->line('');
        }

        // Resulting DTO Data
        if ($result['dto_data']) {
            $this->info('📄 Resulting DTO Data:');
            $this->line(json_encode($result['dto_data'], JSON_PRETTY_PRINT));
        }
    }

    /**
     * Resolve class name with common namespaces.
     */
    private function resolveClassName(string $className): string
    {
        // If already fully qualified, return as is
        if (str_contains($className, '\\')) {
            return $className;
        }

        // Try common DTO namespaces
        $namespaces = [
            'App\\Data\\',
            'App\\DTOs\\',
            'App\\DTO\\',
            'Grazulex\\Arc\\Examples\\', // For examples
            'App\\',
            '', // Global namespace
        ];

        foreach ($namespaces as $namespace) {
            $fullClassName = $namespace . $className;
            if (class_exists($fullClassName)) {
                return $fullClassName;
            }
        }

        return $className;
    }
}
