<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Console\Commands;

use Exception;
use Grazulex\LaravelArc\Support\DtoPaths;
use Grazulex\LaravelArc\Support\Export\OpenApi\OpenApiSchemaGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

final class DtoGenerateOpenApiCommand extends Command
{
    protected $signature = 'dto:generate-openapi
        {filenames?* : YAML filenames to export (relative to the definitions path); every definition when omitted}
        {--output= : File written (defaults to config dto.openapi_path)}
        {--format= : Output format: json or yaml (guessed from the output extension, json by default)}
        {--openapi=3.1 : OpenAPI version of the document: 3.0 or 3.1}
        {--title=DTO Schemas : Value of info.title}
        {--api-version=1.0.0 : Value of info.version}
        {--components-only : Only write the components object, to merge it into an existing document}
        {--force : Overwrite an existing file}
        {--dry-run : Output the result to console instead of saving}
        {--check : Compare the rendered document with the existing file and fail on any difference, without writing}';

    protected $description = 'Generate OpenAPI schemas (components/schemas) from YAML DTO definitions.';

    public function handle(): int
    {
        $files = $this->definitionFiles();
        if ($files === null) {
            return self::FAILURE;
        }

        $format = $this->resolveFormat();
        if ($format === null) {
            $this->error('Unsupported format: '.$this->option('format').' (use json or yaml)');

            return self::FAILURE;
        }

        try {
            $generator = new OpenApiSchemaGenerator((string) $this->option('openapi'));

            $definitions = [];
            foreach ($files as $file) {
                $definition = Yaml::parseFile($file);
                if (! is_array($definition)) {
                    $this->error('❌ Invalid YAML definition: '.basename($file));

                    return self::FAILURE;
                }

                $definitions[] = $definition;
            }

            $document = $this->option('components-only')
                ? ['components' => $generator->generateComponents($definitions)]
                : $generator->generateDocument($definitions, [
                    'title' => (string) $this->option('title'),
                    'version' => (string) $this->option('api-version'),
                ]);

            $content = $format === 'yaml' ? $generator->toYaml($document) : $generator->toJson($document);
        } catch (Exception $e) {
            $this->error("❌ Failed to generate the OpenAPI document: {$e->getMessage()}");

            return self::FAILURE;
        }

        foreach ($generator->missingReferences($document) as $missing) {
            $this->warn("Schema {$missing} is referenced but has no YAML definition in this export.");
        }

        $outputPath = $this->outputPath($format);

        if ($this->option('check')) {
            return $this->checkFile($outputPath, $content);
        }

        if ($this->option('dry-run')) {
            $this->line("\n----- Begin OpenAPI -----\n");
            $this->line($content);
            $this->line("----- End OpenAPI -----\n");

            return self::SUCCESS;
        }

        if (File::exists($outputPath) && ! $this->option('force')) {
            $this->warn("File already exists: $outputPath (use --force to overwrite)");

            return self::FAILURE;
        }

        try {
            File::ensureDirectoryExists(dirname($outputPath));
            File::put($outputPath, $content);
        } catch (Exception $e) {
            $this->error("❌ Failed to write $outputPath: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('✅ OpenAPI schemas ('.count($files).") written to: $outputPath");

        return self::SUCCESS;
    }

    /**
     * @return list<string>|null
     */
    private function definitionFiles(): ?array
    {
        $basePath = DtoPaths::definitionDir();
        $filenames = array_values(array_filter((array) $this->argument('filenames'), 'is_string'));

        if ($filenames === []) {
            $files = array_merge(File::glob($basePath.'/*.yaml'), File::glob($basePath.'/*.yml'));
            sort($files);

            if ($files === []) {
                $this->error('No YAML files found in directory: '.$basePath);

                return null;
            }

            return $files;
        }

        $files = [];
        foreach ($filenames as $filename) {
            $fullPath = $basePath.'/'.$filename;
            if (! File::exists($fullPath)) {
                $this->error("YAML file not found: $fullPath");

                return null;
            }

            $files[] = $fullPath;
        }

        return $files;
    }

    private function resolveFormat(): ?string
    {
        $format = $this->option('format');

        if (is_string($format) && $format !== '') {
            $format = mb_strtolower($format);

            return match ($format) {
                'json' => 'json',
                'yaml', 'yml' => 'yaml',
                default => null,
            };
        }

        $output = $this->option('output');
        $extension = is_string($output) ? mb_strtolower(pathinfo($output, PATHINFO_EXTENSION)) : '';

        return in_array($extension, ['yaml', 'yml'], true) ? 'yaml' : 'json';
    }

    private function outputPath(string $format): string
    {
        $output = $this->option('output');
        if (is_string($output) && $output !== '') {
            return $output;
        }

        $path = (string) config('dto.openapi_path', base_path('openapi/dto-schemas.json'));

        if ($format === 'yaml' && mb_strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'json') {
            return mb_substr($path, 0, -5).'.yaml';
        }

        return $path;
    }

    private function checkFile(string $outputPath, string $content): int
    {
        if (! File::exists($outputPath)) {
            $this->error("❌ OpenAPI file is missing: $outputPath");

            return self::FAILURE;
        }

        if (str_replace("\r\n", "\n", File::get($outputPath)) !== str_replace("\r\n", "\n", $content)) {
            $this->error("❌ OpenAPI file is out of date: $outputPath");

            return self::FAILURE;
        }

        $this->info("✅ OpenAPI file is up to date: $outputPath");

        return self::SUCCESS;
    }
}
