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
                            {--compact : Display only DTO names}';

    protected $description = 'List all available DTO YAML definition files';

    public function handle(): int
    {
        $rawPath = $this->option('path') ?? DtoPaths::definitionDir();
        $path = realpath($rawPath) ?: $rawPath;

        $compact = $this->option('compact');

        if (! File::isDirectory($path)) {
            $this->error("Directory not found: $path");

            return Command::FAILURE;
        }

        $files = collect(File::files($path))
            ->filter(fn ($file): bool => $file->getExtension() === 'yaml')
            ->sortBy(fn ($file) => $file->getFilename());

        if ($files->isEmpty()) {
            $this->warn("No DTO definition files found in $path");

            return Command::SUCCESS;
        }

        $this->info("📂 DTO definition files in: $path\n");

        foreach ($files as $file) {
            $basename = $file->getFilenameWithoutExtension();
            $yamlData = Yaml::parseFile($file->getRealPath());

            // Récupérer le nom du DTO du fichier YAML, sinon le générer à partir du nom du fichier
            $dtoName = $yamlData['header']['dto'] ?? $this->generateDtoNameFromFilename($basename);

            $fieldCount = count($yamlData['fields'] ?? []);
            $relationCount = count($yamlData['relations'] ?? []);
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
