<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Console\Commands;

use Grazulex\LaravelArc\Generator\DtoGenerator;
use Grazulex\LaravelArc\Support\DtoPaths;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
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
        $this->info("🛠 Generating DTO from: $filePath");

        $yaml = Yaml::parseFile($filePath);
        $dtoName = $yaml['header']['dto'] ?? null;
        $namespace = $yaml['options']['namespace'] ?? 'App\\DTOs';

        if (! $dtoName) {
            $this->error('Missing "dto" in header section.');

            return self::FAILURE;
        }

        $code = DtoGenerator::make()->generateFromDefinition($yaml);

        if ($this->option('dry-run')) {
            $this->line("\n----- Begin DTO: $dtoName -----\n");
            $this->line($code);
            $this->line("\n----- End DTO: $dtoName -----\n");

            return self::SUCCESS;
        }

        $outputPath = $this->option('output')
            ?? $this->resolveOutputPath($dtoName, $namespace);

        if (File::exists($outputPath) && ! $this->option('force')) {
            $this->warn("File already exists: $outputPath (use --force to overwrite)");

            return self::FAILURE;
        }

        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $code);

        $this->info("✅ DTO class written to: $outputPath");

        return self::SUCCESS;
    }

    private function resolveOutputPath(string $dtoName, string $namespace): string
    {
        // Utilise le chemin de sortie configuré comme base
        $outputDir = DtoPaths::dtoOutputDir();

        // Pour les namespaces qui correspondent au schéma App\DTOs,
        // utilise le chemin configuré directement
        if (str_starts_with($namespace, 'App\\DTOs') || str_starts_with($namespace, 'App\\DTO')) {
            return $outputDir.'/'.$dtoName.'.php';
        }

        // Pour les autres namespaces, crée un sous-dossier basé sur le namespace
        $subPath = str_replace(['App\\', '\\'], ['', '/'], $namespace);

        return $outputDir.'/'.$subPath.'/'.$dtoName.'.php';
    }
}
