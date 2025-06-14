<?php

namespace Grazulex\Arc\Commands;

use Exception;

use function function_exists;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

use function is_string;

class MakeDtoCommand extends Command
{
    protected $signature = 'make:dto {name} {--model= : The model to base the DTO on} {--path=app/Data : The path where to create the DTO}';

    protected $description = 'Create a new DTO class';

    public function handle(): int
    {
        $name = $this->argument('name');
        $modelName = $this->option('model');
        $path = $this->option('path');

        // Ensure the name ends with DTO
        if (!Str::endsWith($name, 'DTO')) {
            $name .= 'DTO';
        }

        // Create the directory if it doesn't exist
        $fullPath = $this->getFullPath($path);
        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        // Generate DTO content
        $dtoContent = $this->generateDtoContent($name, $modelName);

        // Write the file
        $fileName = $name . '.php';
        $filePath = $fullPath . '/' . $fileName;

        if (File::exists($filePath)) {
            $this->error("DTO {$fileName} already exists!");

            return self::FAILURE;
        }

        File::put($filePath, $dtoContent);

        $this->info("DTO {$fileName} created successfully in {$path}!");

        return self::SUCCESS;
    }

    private function generateDtoContent(string $name, ?string $modelName): string
    {
        $namespace = $this->getNamespaceFromPath($this->option('path'));
        $properties = [];

        if ($modelName) {
            $properties = $this->extractPropertiesFromModel($modelName);
        }

        $propertiesCode = $this->generatePropertiesCode($properties);

        return <<<PHP
<?php

namespace {$namespace};

use Grazulex\Arc\LaravelArcDTO;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Attributes\DateProperty;
use Grazulex\Arc\Attributes\EnumProperty;
use Grazulex\Arc\Attributes\NestedProperty;
use Carbon\Carbon;

class {$name} extends LaravelArcDTO
{
{$propertiesCode}
}

PHP;
    }

    private function getNamespaceFromPath(string $path): string
    {
        // Convert path to namespace
        $namespace = str_replace('/', '\\', $path);
        $namespace = str_replace('app\\', 'App\\', $namespace);

        return str_replace('App\\App\\', 'App\\', $namespace); // Avoid double App
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function extractPropertiesFromModel(string $modelName): array
    {
        $modelClass = $this->resolveModelClass($modelName);

        if (!class_exists($modelClass)) {
            $this->warn("Model {$modelClass} not found. Creating empty DTO.");

            return [];
        }

        try {
            $model = new $modelClass();
            $properties = [];

            // Get fillable attributes
            if (method_exists($model, 'getFillable')) {
                $fillable = $model->getFillable();
                foreach ($fillable as $attribute) {
                    $properties[$attribute] = $this->guessPropertyType($attribute, $model);
                }
            }

            // Get dates
            if (method_exists($model, 'getDates') || property_exists($model, 'dates')) {
                $dates = method_exists($model, 'getDates') ? $model->getDates() : ($model->dates ?? []);
                foreach ($dates as $date) {
                    $properties[$date] = ['type' => 'date', 'nullable' => true];
                }
            }

            // Common timestamps
            if (method_exists($model, 'getTimestamps') && $model->getTimestamps()) {
                $properties['created_at'] = ['type' => 'date', 'nullable' => true];
                $properties['updated_at'] = ['type' => 'date', 'nullable' => true];
            }

            return $properties;
        } catch (Exception $e) {
            $this->warn("Could not analyze model {$modelClass}: {$e->getMessage()}");

            return [];
        }
    }

    private function resolveModelClass(string $modelName): string
    {
        // Remove DTO suffix if present
        $modelName = str_replace('DTO', '', $modelName);

        // Try different namespaces
        $possibleClasses = [
            "App\\Models\\{$modelName}",
            "App\\{$modelName}",
            $modelName,
        ];

        foreach ($possibleClasses as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        return "App\\Models\\{$modelName}";
    }

    /**
     * @return array<string, mixed>
     */
    private function guessPropertyType(string $attribute, object $model): array
    {
        // 1. First, check model casts (most reliable)
        if (method_exists($model, 'getCasts')) {
            $casts = $model->getCasts();
            if (isset($casts[$attribute])) {
                return $this->typeFromCast($casts[$attribute], $attribute);
            }
        }

        // 2. Check database column type (second most reliable)
        $dbType = $this->getColumnTypeFromDatabase($attribute, $model);
        if ($dbType !== null) {
            return $dbType;
        }

        // 3. Check migration files (if available)
        $migrationType = $this->getTypeFromMigrations($attribute, $model);
        if ($migrationType !== null) {
            return $migrationType;
        }

        // 4. Fallback to pattern-based guessing
        return $this->guessTypeFromPattern($attribute);
    }

    /**
     * @return array<string, mixed>
     */
    private function typeFromCast(string $cast, string $attribute): array
    {
        // Handle cast types
        $castType = strtolower($cast);

        return match (true) {
            str_contains($castType, 'int') => ['type' => 'int', 'nullable' => false],
            str_contains($castType, 'float') || str_contains($castType, 'double') || str_contains($castType, 'real') => ['type' => 'float', 'nullable' => false],
            str_contains($castType, 'bool') => ['type' => 'bool', 'nullable' => false],
            str_contains($castType, 'array') || str_contains($castType, 'json') => ['type' => 'array', 'nullable' => true],
            str_contains($castType, 'date') || str_contains($castType, 'datetime') || str_contains($castType, 'timestamp') => ['type' => 'date', 'nullable' => true],
            str_contains($castType, 'decimal') => ['type' => 'float', 'nullable' => false],
            str_contains($castType, 'enum') => ['type' => 'string', 'nullable' => false], // Could be enhanced to detect actual enum
            default => ['type' => 'string', 'nullable' => true]
        };
    }

    /**
     * @return null|array<string, mixed>
     */
    private function getColumnTypeFromDatabase(string $attribute, object $model): ?array
    {
        try {
            if (!method_exists($model, 'getTable')) {
                return null;
            }

            $table = $model->getTable();

            // Check if table exists
            if (!Schema::hasTable($table)) {
                return null;
            }

            // Check if column exists
            if (!Schema::hasColumn($table, $attribute)) {
                return null;
            }

            // Get column type from database
            $columnType = Schema::getColumnType($table, $attribute);
            $columnListing = Schema::getColumnListing($table);

            // Get additional column information if possible
            $isNullable = true; // Default to nullable

            try {
                $columns = DB::select("SHOW COLUMNS FROM {$table} LIKE '{$attribute}'");
                if (!empty($columns)) {
                    $column = $columns[0];
                    $isNullable = strtolower($column->Null ?? 'yes') === 'yes';
                }
            } catch (Exception $e) {
                // Ignore database inspection errors
            }

            return $this->mapDatabaseTypeToPhpType($columnType, $attribute, $isNullable);
        } catch (Exception $e) {
            // If database inspection fails, return null to continue with other methods
            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDatabaseTypeToPhpType(string $dbType, string $attribute, bool $isNullable): array
    {
        $type = strtolower($dbType);
        $config = ['nullable' => $isNullable];

        $config['type'] = match (true) {
            str_contains($type, 'int') || str_contains($type, 'bigint') || str_contains($type, 'smallint') || str_contains($type, 'tinyint') => 'int',
            str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double') || str_contains($type, 'real') => 'float',
            str_contains($type, 'bool') || $type === 'tinyint(1)' => 'bool',
            str_contains($type, 'json') => 'array',
            str_contains($type, 'date') || str_contains($type, 'time') || str_contains($type, 'timestamp') => 'date',
            str_contains($type, 'enum') => 'string', // Could be enhanced to detect actual enum values
            default => 'string'
        };

        // Add validation based on attribute name
        if (str_contains($attribute, 'email')) {
            $config['validation'] = 'email';
        } elseif (str_contains($attribute, 'url')) {
            $config['validation'] = 'url';
        }

        // Set default values for boolean fields
        if ($config['type'] === 'bool' && (str_starts_with($attribute, 'is_') || str_starts_with($attribute, 'has_'))) {
            $config['default'] = false;
        }

        return $config;
    }

    /**
     * @return null|array<string, mixed>
     */
    private function getTypeFromMigrations(string $attribute, object $model): ?array
    {
        try {
            if (!method_exists($model, 'getTable')) {
                return null;
            }

            $table = $model->getTable();
            $migrationPath = $this->getDatabasePath('migrations');

            if (!is_dir($migrationPath)) {
                return null;
            }

            // Look for migration files
            $migrationFiles = glob($migrationPath . '/*_create_' . $table . '_table.php');
            if (empty($migrationFiles)) {
                // Try singular form
                $singularTable = Str::singular($table);
                $migrationFiles = glob($migrationPath . '/*_create_' . $singularTable . '_table.php');
            }

            if (empty($migrationFiles)) {
                return null;
            }

            // Parse the most recent migration file
            $migrationFile = end($migrationFiles);
            $content = file_get_contents($migrationFile);

            if ($content === false) {
                return null;
            }

            // Extract column definition using regex
            $patterns = [
                "/\\\$table->([a-zA-Z]+)\\(\\s*['\"]" . preg_quote($attribute, '/') . "['\"]\\s*\\)/",
                "/\\\$table->([a-zA-Z]+)\\(\\s*['\"]" . preg_quote($attribute, '/') . "['\"]\\s*,.*?\\)/",
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content, $matches)) {
                    return $this->mapMigrationTypeToPhpType($matches[1], $attribute, $content);
                }
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mapMigrationTypeToPhpType(string $migrationMethod, string $attribute, string $migrationContent): array
    {
        // Simple nullable detection - could be improved
        $attributePattern = preg_quote($attribute, '/');
        $regex = "/['\"]{$attributePattern}['\"].*?->nullable\(\)/";
        $isNullable = preg_match($regex, $migrationContent) > 0;

        $config = ['nullable' => $isNullable];

        $config['type'] = match ($migrationMethod) {
            'id', 'bigIncrements', 'increments', 'mediumIncrements', 'smallIncrements', 'tinyIncrements' => 'int',
            'integer', 'bigInteger', 'mediumInteger', 'smallInteger', 'tinyInteger', 'unsignedBigInteger', 'unsignedInteger', 'unsignedMediumInteger', 'unsignedSmallInteger', 'unsignedTinyInteger' => 'int',
            'decimal', 'double', 'float', 'unsignedDecimal' => 'float',
            'boolean' => 'bool',
            'json', 'jsonb' => 'array',
            'date', 'dateTime', 'dateTimeTz', 'time', 'timeTz', 'timestamp', 'timestampTz', 'timestamps', 'timestampsTz' => 'date',
            'enum' => 'string', // Could be enhanced to extract enum values
            default => 'string'
        };

        // Add validation based on attribute name
        if (str_contains($attribute, 'email')) {
            $config['validation'] = 'email';
        } elseif (str_contains($attribute, 'url')) {
            $config['validation'] = 'url';
        }

        // Set default values for boolean fields
        if ($config['type'] === 'bool' && (str_starts_with($attribute, 'is_') || str_starts_with($attribute, 'has_'))) {
            $config['default'] = false;
        }

        return $config;
    }

    /**
     * Fallback pattern-based type guessing.
     *
     * @return array<string, mixed>
     */
    private function guessTypeFromPattern(string $attribute): array
    {
        $patterns = [
            '/.*_id$/' => ['type' => 'int', 'nullable' => false],
            '/^id$/' => ['type' => 'int', 'nullable' => false],
            '/.*_at$/' => ['type' => 'date', 'nullable' => true],
            '/email/' => ['type' => 'string', 'nullable' => false, 'validation' => 'email'],
            '/password/' => ['type' => 'string', 'nullable' => false],
            '/phone/' => ['type' => 'string', 'nullable' => true],
            '/url/' => ['type' => 'string', 'nullable' => true, 'validation' => 'url'],
            '/price|amount|cost/' => ['type' => 'float', 'nullable' => false],
            '/count|quantity|number/' => ['type' => 'int', 'nullable' => false],
            '/is_|has_/' => ['type' => 'bool', 'nullable' => false, 'default' => false],
        ];

        foreach ($patterns as $pattern => $config) {
            if (preg_match($pattern, $attribute)) {
                return $config;
            }
        }

        // Default to string
        return ['type' => 'string', 'nullable' => true];
    }

    /**
     * @param array<string, array<string, mixed>> $properties
     */
    private function generatePropertiesCode(array $properties): string
    {
        if (empty($properties)) {
            return "    // Add your properties here\n    // Example:\n    // #[Property(type: 'string', required: true)]\n    // public string \$name;";
        }

        $code = [];
        foreach ($properties as $name => $config) {
            $code[] = $this->generatePropertyCode($name, $config);
        }

        return implode("\n\n", $code);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function generatePropertyCode(string $name, array $config): string
    {
        $type = $config['type'] ?? 'string';
        $nullable = $config['nullable'] ?? true;
        $default = $config['default'] ?? null;
        $validation = $config['validation'] ?? null;

        $phpType = $this->getPhpType($type, $nullable);
        $attributeParams = [];

        if ($type === 'date') {
            $attribute = 'DateProperty';
            $attributeParams[] = 'required: ' . ($nullable ? 'false' : 'true');
        } else {
            $attribute = 'Property';
            $attributeParams[] = "type: '{$type}'";
            $attributeParams[] = 'required: ' . ($nullable ? 'false' : 'true');
        }

        if ($default !== null) {
            $defaultValue = is_string($default) ? "'{$default}'" : ($default ? 'true' : 'false');
            $attributeParams[] = "default: {$defaultValue}";
        }

        if ($validation) {
            $attributeParams[] = "validation: '{$validation}'";
        }

        $attributeString = implode(', ', $attributeParams);

        return "    #[{$attribute}({$attributeString})]\n    public {$phpType} \${$name};";
    }

    private function getPhpType(string $type, bool $nullable): string
    {
        $phpType = match ($type) {
            'int' => 'int',
            'float' => 'float',
            'bool' => 'bool',
            'array' => 'array',
            'date' => 'Carbon',
            default => 'string'
        };

        return $nullable ? "?{$phpType}" : $phpType;
    }

    private function getFullPath(string $path): string
    {
        // In Laravel apps, use base_path()
        if (function_exists('base_path')) {
            return base_path($path);
        }

        // For testing or standalone usage, use current directory
        return getcwd() . '/' . $path;
    }

    private function getDatabasePath(string $path = ''): string
    {
        // In Laravel apps, use database_path()
        if (function_exists('database_path')) {
            return database_path($path);
        }

        // For testing or standalone usage, fallback to base_path/database
        if (function_exists('base_path')) {
            return base_path('database/' . $path);
        }

        // Last resort
        return getcwd() . '/database/' . $path;
    }
}
