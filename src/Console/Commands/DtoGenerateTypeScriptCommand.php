<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Console\Commands;

use Exception;
use Grazulex\LaravelArc\Generator\TypeScriptGenerator;
use Grazulex\LaravelArc\Support\DtoPaths;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

final class DtoGenerateTypeScriptCommand extends Command
{
    protected $signature = 'dto:generate-ts
        {filename? : The YAML filename to export (relative to the definitions path)}
        {--all : Export every YAML definition in the definitions path}
        {--path= : Directory where the .ts files are written (defaults to config dto.typescript_path)}
        {--type : Emit "export type" aliases instead of interfaces}
        {--force : Overwrite existing TypeScript files}
        {--dry-run : Output the result to console instead of saving}
        {--check : Compare the rendered TypeScript with the existing files and fail on any difference, without writing}';

    protected $description = 'Generate TypeScript types from YAML DTO definitions.';

    public function handle(): int
    {
        $basePath = DtoPaths::definitionDir();

        if ($this->option('all')) {
            $files = File::glob($basePath.'/*.yaml');
            if (empty($files)) {
                $this->error('No YAML files found in directory: '.$basePath);

                return self::FAILURE;
            }

            $failed = 0;
            foreach ($files as $file) {
                if ($this->exportFile($file) !== self::SUCCESS) {
                    $failed++;
                }
            }

            if ($failed > 0) {
                $this->error($this->option('check')
                    ? "❌ {$failed} TypeScript file(s) are out of date or could not be checked."
                    : "❌ {$failed} TypeScript file(s) could not be generated.");

                return self::FAILURE;
            }

            if ($this->option('check')) {
                $this->info('✅ All TypeScript files are up to date.');
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

        return $this->exportFile($fullPath);
    }

    private function exportFile(string $filePath): int
    {
        try {
            $definition = Yaml::parseFile($filePath);
            if (! is_array($definition)) {
                $this->error('❌ Invalid YAML definition: '.basename($filePath));

                return self::FAILURE;
            }

            $generator = new TypeScriptGenerator();
            $typeName = $generator->resolveTypeName($definition);
            $code = $generator->generate($definition, [
                'style' => $this->option('type') ? 'type' : 'interface',
                'imports' => true,
            ]);
        } catch (Exception $e) {
            $this->error('❌ Failed to export '.basename($filePath).": {$e->getMessage()}");

            return self::FAILURE;
        }

        $outputPath = $this->outputDirectory().'/'.$typeName.'.ts';

        if ($this->option('check')) {
            return $this->checkFile($outputPath, $code);
        }

        if ($this->option('dry-run')) {
            $this->line("\n----- Begin TypeScript: $typeName -----\n");
            $this->line($code);
            $this->line("----- End TypeScript: $typeName -----\n");

            return self::SUCCESS;
        }

        if (File::exists($outputPath) && ! $this->option('force')) {
            $this->warn("File already exists: $outputPath (use --force to overwrite)");

            return self::FAILURE;
        }

        try {
            File::ensureDirectoryExists(dirname($outputPath));
            File::put($outputPath, $code);
        } catch (Exception $e) {
            $this->error("❌ Failed to write $outputPath: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info("✅ TypeScript written to: $outputPath");

        return self::SUCCESS;
    }

    private function checkFile(string $outputPath, string $code): int
    {
        if (! File::exists($outputPath)) {
            $this->error("❌ TypeScript file is missing: $outputPath");

            return self::FAILURE;
        }

        if (str_replace("\r\n", "\n", File::get($outputPath)) !== str_replace("\r\n", "\n", $code)) {
            $this->error("❌ TypeScript file is out of date: $outputPath");

            return self::FAILURE;
        }

        $this->info("✅ TypeScript file is up to date: $outputPath");

        return self::SUCCESS;
    }

    private function outputDirectory(): string
    {
        $path = $this->option('path') ?: config('dto.typescript_path', resource_path('js/types'));

        return mb_rtrim((string) $path, '/\\');
    }
}
