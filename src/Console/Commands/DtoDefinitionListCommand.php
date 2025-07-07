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
        // ✅ Résout correctement le path même en test
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
            $dtoName = ucfirst($basename).'DTO';
            $yamlData = Yaml::parseFile($file->getRealPath());

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
}
