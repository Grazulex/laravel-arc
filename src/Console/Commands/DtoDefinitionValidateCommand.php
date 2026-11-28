<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Console\Commands;

use Grazulex\LaravelArc\Support\DtoPaths;
use Grazulex\LaravelArc\Support\Validation\DtoDefinitionValidator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Formatter\OutputFormatter;

final class DtoDefinitionValidateCommand extends Command
{
    protected $signature = 'dto:definition-validate
                            {filenames?* : YAML filenames to validate (relative to the definitions path)}
                            {--all : Validate every YAML definition in the definitions path}
                            {--path= : Directory containing DTO YAML definitions (overrides config)}
                            {--json : Output results as JSON}';

    protected $description = 'Validate DTO YAML definition files without generating anything';

    public function handle(): int
    {
        $pathOption = $this->option('path');
        $rawPath = is_string($pathOption) && $pathOption !== '' ? $pathOption : DtoPaths::definitionDir();
        $path = realpath($rawPath) ?: $rawPath;

        if (! File::isDirectory($path)) {
            $this->error("Directory not found: $path");

            return self::FAILURE;
        }

        $available = array_merge(File::glob($path.'/*.yaml'), File::glob($path.'/*.yml'));
        sort($available);

        $files = $this->filesToValidate($path, $available);
        if ($files === null) {
            return self::FAILURE;
        }

        $validator = new DtoDefinitionValidator();
        $knownDtos = DtoDefinitionValidator::declaredDtoNames($available);

        $results = [];
        foreach ($files as $file) {
            $result = $validator->validateFile($file, $knownDtos);

            $results[] = [
                'file' => basename($file),
                'dto' => $result['dto'],
                'valid' => $result['errors'] === [],
                'errors' => $result['errors'],
                'warnings' => $result['warnings'],
            ];
        }

        $invalid = count(array_filter($results, fn (array $result): bool => ! $result['valid']));

        if ($this->option('json')) {
            $this->line((string) json_encode(
                ['valid' => $invalid === 0, 'files' => $results],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ));
        } else {
            $this->render($results, $path, $invalid);
        }

        return $invalid === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @param  list<string>  $available
     * @return list<string>|null
     */
    private function filesToValidate(string $path, array $available): ?array
    {
        if ($this->option('all')) {
            if ($available === []) {
                $this->error('No YAML files found in directory: '.$path);

                return null;
            }

            return $available;
        }

        $filenames = array_values(array_filter((array) $this->argument('filenames'), 'is_string'));

        if ($filenames === []) {
            $this->error('Please provide a YAML filename or use --all');

            return null;
        }

        $files = [];
        foreach ($filenames as $filename) {
            $fullPath = $path.'/'.$filename;

            // Allow "user" as a shortcut for "user.yaml"
            if (! File::exists($fullPath) && pathinfo($filename, PATHINFO_EXTENSION) === '' && File::exists($fullPath.'.yaml')) {
                $fullPath .= '.yaml';
            }

            $files[] = $fullPath;
        }

        return $files;
    }

    /**
     * @param  list<array{file: string, dto: string|null, valid: bool, errors: list<string>, warnings: list<string>}>  $results
     */
    private function render(array $results, string $path, int $invalid): void
    {
        $this->info("🔍 Validating DTO definitions in: $path");
        $this->line('');

        $errorCount = 0;
        $warningCount = 0;

        foreach ($results as $result) {
            $name = $result['dto'] !== null ? " ({$result['dto']})" : '';
            $title = OutputFormatter::escape($result['file'].$name);

            $this->line($result['valid'] ? "✅ <info>{$title}</info>" : "❌ <error>{$title}</error>");

            foreach ($result['errors'] as $error) {
                $errorCount++;
                $this->line('   • '.$this->formatMessage($error));
            }

            foreach ($result['warnings'] as $warning) {
                $warningCount++;
                $this->line('   ⚠️  <comment>'.$this->formatMessage($warning).'</comment>');
            }
        }

        $this->line('');

        $total = count($results);
        $details = "{$errorCount} error(s), {$warningCount} warning(s)";

        if ($invalid > 0) {
            $this->error("❌ {$invalid} of {$total} definition(s) invalid: {$details}.");

            return;
        }

        $this->info("✅ {$total} definition(s) valid".($warningCount > 0 ? " ({$details})." : '.'));
    }

    private function formatMessage(string $message): string
    {
        return OutputFormatter::escape(str_replace("\n", "\n     ", mb_trim($message)));
    }
}
