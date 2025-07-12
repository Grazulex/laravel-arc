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
                            {--json : Output as JSON array}';

    protected $description = 'List all available DTO YAML definition files';

    public function handle(): int
    {
        $rawPath = $this->option('path') ?? DtoPaths::definitionDir();
        $path = realpath($rawPath) ?: $rawPath;

        $compact = $this->option('compact');
        $json = $this->option('json');

        if (! File::isDirectory($path)) {
            if ($json) {
                $this->line(json_encode(['error' => "Directory not found: $path"]));
            } else {
                $this->error("Directory not found: $path");
            }

            return Command::FAILURE;
        }

        $files = collect(File::files($path))
            ->filter(fn ($file): bool => $file->getExtension() === 'yaml')
            ->sortBy(fn ($file) => $file->getFilename());

        if ($files->isEmpty()) {
            if ($json) {
                $this->line(json_encode([]));
            } else {
                $this->warn("No DTO definition files found in $path");
            }

            return Command::SUCCESS;
        }

        if ($json) {
            $jsonData = [];
            foreach ($files as $file) {
                $basename = $file->getFilenameWithoutExtension();
                $yamlData = Yaml::parseFile($file->getRealPath());

                // Récupérer le nom du DTO du fichier YAML, sinon le générer à partir du nom du fichier
                $dtoName = $yamlData['dto'] ?? $yamlData['header']['dto'] ?? $this->generateDtoNameFromFilename($basename);

                $dtoPath = DtoPaths::dtoFilePath($dtoName);
                $dtoExists = File::exists($dtoPath);

                $fields = $yamlData['fields'] ?? [];
                $relations = $yamlData['relations'] ?? [];

                $jsonData[] = [
                    'dto' => $dtoName,
                    'model' => $yamlData['model'] ?? $yamlData['header']['model'] ?? null,
                    'table' => $yamlData['table'] ?? $yamlData['header']['table'] ?? null,
                    'fields' => $fields,
                    'relations' => $relations,
                    'dtoExists' => $dtoExists,
                    'yamlFile' => $file->getFilename(),
                    'dtoPath' => $dtoExists ? str_replace(base_path().'/', '', $dtoPath) : null,
                ];
            }

            $this->line(json_encode($jsonData));
        } else {
            $this->info("📂 DTO definition files in: $path\n");

            foreach ($files as $file) {
                $basename = $file->getFilenameWithoutExtension();
                $yamlData = Yaml::parseFile($file->getRealPath());

                // Récupérer le nom du DTO du fichier YAML, sinon le générer à partir du nom du fichier
                $dtoName = $yamlData['dto'] ?? $yamlData['header']['dto'] ?? $this->generateDtoNameFromFilename($basename);

                $fields = $yamlData['fields'] ?? [];
                $relations = $yamlData['relations'] ?? [];
                
                // Handle both array and object formats for fields and relations
                $fieldCount = is_array($fields) ? count($fields) : 0;
                $relationCount = is_array($relations) ? count($relations) : 0;
                $dtoPath = DtoPaths::dtoFilePath($dtoName);
                $dtoExists = File::exists($dtoPath);

                if ($compact) {
                    $this->line("- $dtoName");
                } else {
                    $this->line("✔ $dtoName  =>  ".$file->getFilename());
                    $this->line("   • {$fieldCount} field(s), {$relationCount} relation(s)");
                    $this->line('   • DTO class exists: '.($dtoExists ? '✅ '.str_replace(base_path().'/', '', $dtoPath) : '❌'));
                    $this->line('');
                }
            }
        }

        return Command::SUCCESS;
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
