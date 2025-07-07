<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Console\Commands;

use Grazulex\LaravelArc\Support\DtoPaths;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class DtoDefinitionInitCommand extends Command
{
    protected $signature = 'dto:definition-init
                            {name : The name of the DTO (e.g. UserDTO)}
                            {--model= : Fully qualified model class (e.g. App\\Models\\User)}
                            {--table= : Table name (e.g. users)}
                            {--path= : Optional path to store the YAML file (overrides config)}
                            {--force : Overwrite existing file}';

    protected $description = 'Initialize a YAML descriptor file for a DTO';

    public function handle(): int
    {
        $name = $this->argument('name');
        $model = $this->option('model');
        $table = $this->option('table');
        $force = $this->option('force');

        if (! $model || ! $table) {
            $this->error('The --model and --table options are required.');

            return Command::FAILURE;
        }

        $basePath = $this->option('path')
    ? mb_rtrim($this->option('path'), '/')
    : DtoPaths::definitionDir();

        File::ensureDirectoryExists($basePath);

        $filename = $basePath.'/'.mb_strtolower(str_replace('DTO', '', $name)).'.yaml';

        if (File::exists($filename) && ! $force) {
            $this->warn("File already exists at $filename. Use --force to overwrite.");

            return Command::FAILURE;
        }

        $yaml = $this->buildYamlTemplate($name, $model, $table);

        File::put($filename, $yaml);

        $this->info("DTO YAML created at: $filename");

        return Command::SUCCESS;
    }

    private function buildYamlTemplate(string $name, string $model, string $table): string
    {
        $namespace = DtoPaths::dtoNamespace();

        return <<<YAML
dto: {$name}
model: {$model}
table: {$table}

fields:
  # Example field, replace or expand manually
  - name: id
    type: int
    nullable: false
    readonly: true

relations: []

options:
  timestamps: true
  soft_deletes: false
  expose_hidden_by_default: false
  namespace: {$namespace}
YAML;
    }
}
