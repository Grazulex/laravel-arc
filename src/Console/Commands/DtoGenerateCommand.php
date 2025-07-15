<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Console\Commands;

use Exception;
use Grazulex\LaravelArc\Exceptions\DtoGenerationException;
use Grazulex\LaravelArc\Generator\DtoGenerator;
use Grazulex\LaravelArc\Support\DtoPathResolver;
use Grazulex\LaravelArc\Support\DtoPaths;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;
use Symfony\Component\Yaml\Yaml;

final class DtoGenerateCommand extends Command
{
    protected $signature = 'dto:generate
        {filename? : The YAML filename to generate (relative to config)}
        {--force : Overwrite existing DTO file if present}
        {--output= : Manually specify output path for generated DTO}
        {--dry-run : Output the result to console instead of saving}
        {--all : Generate all YAML files in the base path}';

    protected $description = 'Generate a full DTO PHP class from a YAML definition.';

    public function handle(): int
    {
        $basePath = DtoPaths::definitionDir();

        if ($this->option('all')) {
            $files = File::glob($basePath.'/*.yaml');
            if (empty($files)) {
                $this->error('No YAML files found in directory: '.$basePath);

                return self::FAILURE;
            }
            foreach ($files as $file) {
                $this->generateFromFile($file);
            }

            return self::SUCCESS;
        }

        $filename = $this->argument('filename');
        if (! $filename) {
            $this->error('Please provide a YAML filename or use --all');

            return self::FAILURE;
        }

        $fullPath = $basePath.'/'.$filename;
        if (! File::exists($fullPath)) {
            $this->error("YAML file not found: $fullPath");

            return self::FAILURE;
        }

        return $this->generateFromFile($fullPath);
    }

    private function generateFromFile(string $filePath): int
    {
        try {
            $this->info("🛠 Generating DTO from: $filePath");

            // Parse YAML with error handling
            try {
                $yaml = Yaml::parseFile($filePath);
            } catch (YamlParseException $e) {
                $originalMessage = $e->getMessage();
                $fileName = basename($filePath);

                if (str_contains($originalMessage, 'Multiple documents are not supported')) {
                    throw DtoGenerationException::yamlParsingError(
                        $filePath,
                        "Multiple YAML documents detected in '{$fileName}'. ".
                        'Each DTO definition must be in a separate file. '.
                        'If you have multiple DTOs, split them into separate YAML files.',
                        $e
                    );
                }

                if (str_contains($originalMessage, 'Complex mappings are not supported')) {
                    throw DtoGenerationException::yamlParsingError(
                        $filePath,
                        "Complex YAML mapping detected in '{$fileName}'. ".
                        'Please use simple key-value pairs and avoid complex YAML structures.',
                        $e
                    );
                }

                // Generic YAML parsing error
                throw DtoGenerationException::yamlParsingError(
                    $filePath,
                    "Invalid YAML syntax in '{$fileName}': {$originalMessage}",
                    $e
                );
            }

            // Validate required header information
            $dtoName = $yaml['header']['dto'] ?? $yaml['class_name'] ?? null;
            if (! $dtoName) {
                throw DtoGenerationException::missingHeader($filePath, 'dto or class_name');
            }

            // Support both old and new namespace formats during transition
            $namespace = $yaml['namespace'] ?? $yaml['header']['namespace'] ?? $yaml['options']['namespace'] ?? 'App\\DTO';

            // Validate namespace format
            if (! DtoPathResolver::isValidNamespace($namespace)) {
                throw DtoGenerationException::namespaceResolutionError(
                    $filePath,
                    $namespace,
                    'Invalid namespace format',
                    $dtoName
                );
            }

            // Generate DTO code with error handling
            try {
                $code = DtoGenerator::make()->generateFromDefinition($yaml, $filePath);
            } catch (DtoGenerationException $e) {
                throw $e;
            } catch (Exception $e) {
                throw new DtoGenerationException(
                    "Failed to generate DTO code: {$e->getMessage()}",
                    1020,
                    $e
                );
            }

            if ($this->option('dry-run')) {
                $this->line("\n----- Begin DTO: $dtoName -----\n");
                $this->line($code);
                $this->line("\n----- End DTO: $dtoName -----\n");

                return self::SUCCESS;
            }

            $outputPath = $this->option('output')
                ?? DtoPathResolver::resolveOutputPath($dtoName, $namespace);

            if (File::exists($outputPath) && ! $this->option('force')) {
                $this->warn("File already exists: $outputPath (use --force to overwrite)");

                return self::FAILURE;
            }

            // Write file with error handling
            try {
                File::ensureDirectoryExists(dirname($outputPath));
                File::put($outputPath, $code);
            } catch (Exception $e) {
                throw DtoGenerationException::fileWriteError(
                    $filePath,
                    $outputPath,
                    $e->getMessage(),
                    $dtoName
                );
            }

            $this->info("✅ DTO class written to: $outputPath");

            return self::SUCCESS;
        } catch (DtoGenerationException $e) {
            $this->error($e->getFormattedMessage());

            return self::FAILURE;
        } catch (Exception $e) {
            $this->error("❌ Unexpected error: {$e->getMessage()}");
            if ($this->option('verbose')) {
                $this->error("Stack trace: {$e->getTraceAsString()}");
            }

            return self::FAILURE;
        }
    }
}
