<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Console\Commands;

use Grazulex\LaravelArc\Support\DtoPaths;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

final class DtoDefinitionListCommand extends Command
{
    protected $signature = 'dto:definition-list
                            {--path= : Directory containing DTO YAML definitions (overrides config)}
                            {--compact : Display only DTO names}
                            {--json : Output results as JSON}';

    protected $description = 'List all available DTO YAML definition files';

    public function handle(): int
    {
        $rawPath = $this->option('path') ?? DtoPaths::definitionDir();
        $path = realpath($rawPath) ?: $rawPath;

        $compact = $this->option('compact');
        $outputJson = $this->option('json');

        if (! File::isDirectory($path)) {
            $this->error("Directory not found: $path");

            return Command::FAILURE;
        }

        $files = collect(File::files($path))
            ->filter(fn ($file): bool => $file->getExtension() === 'yaml')
            ->sortBy(fn ($file) => $file->getFilename());

        if ($files->isEmpty()) {
            if ($outputJson) {
                $this->line('[]');
            } else {
                $this->warn("No DTO definition files found in $path");
            }

            return Command::SUCCESS;
        }

        if ($outputJson) {
            $this->outputJson($files);
        } else {
            $this->outputTable($files, $path, $compact);
        }

        return Command::SUCCESS;
    }

    private function outputJson($files): void
    {
        $dtos = [];

        foreach ($files as $file) {
            $basename = $file->getFilenameWithoutExtension();
            $yamlData = Yaml::parseFile($file->getRealPath());

            // Support both new and old YAML formats
            $dtoName = $yamlData['header']['dto']
                ?? $yamlData['dto']
                ?? $this->generateDtoNameFromFilename($basename);

            $namespace = $yamlData['header']['namespace']
                ?? $yamlData['namespace']
                ?? $yamlData['options']['namespace']
                ?? 'App\\DTO';

            $model = $yamlData['header']['model']
                ?? $yamlData['model']
                ?? null;

            $traits = $yamlData['header']['traits']
                ?? $yamlData['traits']
                ?? [];

            $fieldCount = count($yamlData['fields'] ?? []);
            $relationCount = count($yamlData['relations'] ?? []);
            $dtoPath = DtoPaths::dtoFilePath($dtoName);
            $dtoExists = File::exists($dtoPath);

            // Check for deprecated options
            $hasDeprecatedOptions = isset($yamlData['options']);

            $dtos[] = [
                'name' => $dtoName,
                'namespace' => $namespace,
                'model' => $model,
                'traits' => $traits,
                'file' => $file->getFilename(),
                'field_count' => $fieldCount,
                'relation_count' => $relationCount,
                'dto_exists' => $dtoExists,
                'dto_path' => $dtoExists ? str_replace(base_path().'/', '', $dtoPath) : null,
                'has_deprecated_options' => $hasDeprecatedOptions,
            ];
        }

        $this->line(json_encode($dtos, JSON_PRETTY_PRINT));
    }

    private function outputTable($files, string $path, bool $compact): void
    {
        $this->info("📂 DTO definition files in: $path\n");

        foreach ($files as $file) {
            $basename = $file->getFilenameWithoutExtension();
            $yamlData = Yaml::parseFile($file->getRealPath());

            // Support both new and old YAML formats
            $dtoName = $yamlData['header']['dto']
                ?? $yamlData['dto']
                ?? $this->generateDtoNameFromFilename($basename);

            $namespace = $yamlData['header']['namespace']
                ?? $yamlData['namespace']
                ?? $yamlData['options']['namespace']
                ?? 'App\\DTO';

            $model = $yamlData['header']['model']
                ?? $yamlData['model']
                ?? null;

            $traits = $yamlData['header']['traits']
                ?? $yamlData['traits']
                ?? [];

            $fieldCount = count($yamlData['fields'] ?? []);
            $relationCount = count($yamlData['relations'] ?? []);
            $dtoPath = DtoPaths::dtoFilePath($dtoName);
            $dtoExists = File::exists($dtoPath);

            // Format compact
            if ($compact) {
                $this->line("• {$dtoName}");

                continue;
            }

            // Format détaillé
            $this->line("🔹 <info>{$dtoName}</info>");

            // Namespace
            $this->line("   Namespace: <comment>{$namespace}</comment>");

            // Model
            if ($model) {
                $this->line("   Model: <comment>{$model}</comment>");
            }

            // Traits
            if (! empty($traits)) {
                $this->line('   Traits: <comment>'.implode(', ', $traits).'</comment>');
            }

            // Info basique
            $this->line("   File: <comment>{$file->getFilename()}</comment>");
            $this->line("   Fields: <comment>{$fieldCount}</comment>");
            $this->line("   Relations: <comment>{$relationCount}</comment>");

            // Statut du DTO
            if ($dtoExists) {
                $relativePath = str_replace(base_path().'/', '', $dtoPath);
                $this->line("   DTO: <info>✓ Generated</info> ({$relativePath})");
            } else {
                $this->line('   DTO: <comment>✗ Not generated</comment>');
            }

            // Alertes pour les structures dépréciées
            if (isset($yamlData['options'])) {
                $this->line("   ⚠️  <comment>Uses deprecated 'options' structure</comment>");
            }

            $this->line('');
        }
    }

    /**
     * Generate a DTO name from a filename (e.g. "advanced-user" => "AdvancedUserDTO")
     */
    private function generateDtoNameFromFilename(string $filename): string
    {
        // Convertir en PascalCase et ajouter "DTO"
        $normalized = str_replace(['-', '_'], ' ', $filename);
        $pascalCase = str_replace(' ', '', ucwords($normalized));

        return $pascalCase.'DTO';
    }
}
