<?php

namespace Grazulex\Arc\Commands;

use Exception;

use function function_exists;
use function get_class;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

use function in_array;
use function is_object;
use function is_string;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;

class MakeDtoCommand extends Command
{
    protected $signature = 'make:dto {name} {--model= : The model to base the DTO on} {--path=app/Data : The path where to create the DTO} {--with-relations : Include model relations in the DTO} {--relations=* : Specific relations to include} {--with-validation : Generate smart validation rules} {--validation-strict : Use strict validation rules}';

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
            // Try to create model instance safely
            if (!$this->canInstantiateModel($modelClass)) {
                $this->warn("Cannot instantiate model {$modelClass} (no database connection). Using fallback analysis.");

                return $this->extractPropertiesWithoutModel($modelClass);
            }

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

            // Add relations if requested
            if ($this->option('with-relations') || !empty($this->option('relations'))) {
                $relations = $this->extractRelationsFromModel($model);
                $properties = array_merge($properties, $relations);
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

            // Get column information in a database-agnostic way
            $isNullable = true; // Default to nullable

            try {
                // Use database-agnostic approach to get column info
                $connection = DB::connection();
                $driver = $connection->getDriverName();

                if ($driver === 'sqlite') {
                    // SQLite-specific query
                    $columns = DB::select("PRAGMA table_info({$table})");
                    foreach ($columns as $column) {
                        if ($column->name === $attribute) {
                            $isNullable = !$column->notnull; // notnull is 1 for NOT NULL, 0 for NULL
                            break;
                        }
                    }
                } elseif ($driver === 'mysql') {
                    // MySQL-specific query
                    $columns = DB::select("SHOW COLUMNS FROM {$table} LIKE '{$attribute}'");
                    if (!empty($columns)) {
                        $column = $columns[0];
                        $isNullable = strtolower($column->Null ?? 'yes') === 'yes';
                    }
                } elseif ($driver === 'pgsql') {
                    // PostgreSQL-specific query
                    $columns = DB::select('
                        SELECT is_nullable
                        FROM information_schema.columns
                        WHERE table_name = ? AND column_name = ?
                    ', [$table, $attribute]);
                    if (!empty($columns)) {
                        $isNullable = strtolower($columns[0]->is_nullable) === 'yes';
                    }
                }
            } catch (Exception $e) {
                // If database-specific queries fail, try Laravel's Schema facade
                // This is a fallback but might not be as accurate for nullable detection
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

        // Add validation based on attribute name and command options
        if ($this->option('with-validation')) {
            $config['validation'] = $this->generateValidationRules($attribute, $config);
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
        $class = $config['class'] ?? null;
        $relationType = $config['relation_type'] ?? null;
        $relatedModel = $config['related_model'] ?? null;

        $phpType = $this->getPhpType($type, $nullable, $class);
        $attributeParams = [];
        $comment = '';

        // Handle relations with special comments
        if ($relationType && $relatedModel) {
            $modelName = class_basename($relatedModel);
            $comment = "    // Relation: {$relationType} -> {$modelName}\n";
        }

        if ($type === 'date') {
            $attribute = 'Property';
            $attributeParams[] = "type: 'date'";
            $attributeParams[] = 'required: ' . ($nullable ? 'false' : 'true');
        } else {
            $attribute = 'Property';
            $attributeParams[] = "type: '{$type}'";

            // Add class parameter for nested and collection types
            if ($class && in_array($type, ['nested', 'collection', 'enum'], true)) {
                $attributeParams[] = "class: {$class}::class";
            }

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

        return $comment . "    #[{$attribute}({$attributeString})]\n    public {$phpType} \${$name};";
    }

    private function getPhpType(string $type, bool $nullable, ?string $class = null): string
    {
        $phpType = match ($type) {
            'int' => 'int',
            'float' => 'float',
            'bool' => 'bool',
            'array' => 'array',
            'date' => 'Carbon',
            'nested' => $class ? class_basename($class) : 'mixed',
            'collection' => 'array',
            'enum' => $class ? class_basename($class) : 'mixed',
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

    /**
     * Extract relations from Eloquent model using reflection.
     *
     * @return array<string, array<string, mixed>>
     */
    private function extractRelationsFromModel(object $model): array
    {
        $relations = [];
        $specificRelations = $this->option('relations');

        try {
            $reflectionClass = new ReflectionClass($model);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $methodName = $method->getName();

                // Skip if specific relations are requested and this isn't one of them
                if (!empty($specificRelations) && !in_array($methodName, $specificRelations, true)) {
                    continue;
                }

                // Skip magic methods, getters, setters, and Laravel methods
                if ($this->shouldSkipMethod($methodName)) {
                    continue;
                }

                // Try to determine if this is a relation method
                $relationInfo = $this->analyzeRelationMethod($model, $method);
                if ($relationInfo) {
                    $relations[$methodName] = $relationInfo;

                    if ($this->output->isVerbose()) {
                        $this->info("Detected relation: {$methodName} ({$relationInfo['relation_type']}) -> {$relationInfo['related_model']}");
                    }
                }
            }
        } catch (Exception $e) {
            $this->warn("Could not analyze relations: {$e->getMessage()}");
        }

        return $relations;
    }

    /**
     * Check if method should be skipped for relation detection.
     */
    private function shouldSkipMethod(string $methodName): bool
    {
        $skipPrefixes = ['get', 'set', 'is', 'has', 'can', 'should', 'will', 'make', 'create', 'update', 'delete', 'find', 'where', 'scope'];
        $skipMethods = [
            '__construct', '__call', '__callStatic', '__get', '__set', '__isset', '__unset',
            'toArray', 'toJson', 'jsonSerialize', 'save', 'delete', 'fresh', 'refresh',
            'replicate', 'getKey', 'getTable', 'getFillable', 'getGuarded', 'getCasts',
            'getDates', 'getHidden', 'getVisible', 'getAppends', 'getMutatedAttributes',
            'getRelations', 'getConnection', 'newQuery', 'newEloquentBuilder',
            'boot', 'booted', 'booting', 'creating', 'created', 'updating', 'updated',
            'saving', 'saved', 'deleting', 'deleted', 'restoring', 'restored',
            'retrieved', 'observe', 'fill', 'forceFill', 'qualifyColumn', 'removeTableFromKey',
        ];

        // Skip methods with certain prefixes
        foreach ($skipPrefixes as $prefix) {
            if (str_starts_with($methodName, $prefix)) {
                return true;
            }
        }

        // Skip specific methods
        return in_array($methodName, $skipMethods, true);
    }

    /**
     * Analyze a method to determine if it's an Eloquent relation.
     *
     * @return null|array<string, mixed>
     */
    private function analyzeRelationMethod(object $model, ReflectionMethod $method): ?array
    {
        try {
            // Skip methods with parameters (relations usually don't have parameters)
            if ($method->getNumberOfRequiredParameters() > 0) {
                return null;
            }

            // Use reflection to analyze method return type instead of invoking it
            $returnType = $method->getReturnType();
            if ($returnType && $returnType instanceof ReflectionNamedType) {
                $returnTypeName = $returnType->getName();
                if ($this->isEloquentRelation($returnTypeName)) {
                    $relationType = $this->getRelationType($returnTypeName);
                    // For now, we'll use a generic approach since we can't invoke the method safely
                    $methodName = $method->getName();
                    $relatedModelClass = $this->guessRelatedModelFromMethodName($methodName);

                    if ($relationType && $relatedModelClass) {
                        return $this->buildRelationConfig($relationType, $relatedModelClass);
                    }
                }
            }

            // Fallback: Try to invoke method only if we have a database connection AND connection resolver
            if ($this->hasDatabaseConnection() && $this->hasEloquentConnectionResolver()) {
                $result = $method->invoke($model);

                // Check if result is an object (relations should return objects)
                if (!is_object($result)) {
                    return null;
                }

                $resultClass = get_class($result);

                // Check if it's an Eloquent relation
                if (!$this->isEloquentRelation($resultClass)) {
                    return null;
                }

                // Extract relation information
                $relationType = $this->getRelationType($resultClass);
                $relatedModel = $this->getRelatedModelClass($result);

                if (!$relationType || !$relatedModel) {
                    return null;
                }

                // Determine DTO configuration based on relation type
                return $this->buildRelationConfig($relationType, $relatedModel);
            }

            return null;
        } catch (Exception $e) {
            // Method execution failed, probably not a relation or no DB connection
            return null;
        }
    }

    /**
     * Check if class is an Eloquent relation.
     */
    private function isEloquentRelation(string $className): bool
    {
        $relationClasses = [
            'Illuminate\Database\Eloquent\Relations\BelongsTo',
            'Illuminate\Database\Eloquent\Relations\HasOne',
            'Illuminate\Database\Eloquent\Relations\HasMany',
            'Illuminate\Database\Eloquent\Relations\BelongsToMany',
            'Illuminate\Database\Eloquent\Relations\HasOneThrough',
            'Illuminate\Database\Eloquent\Relations\HasManyThrough',
            'Illuminate\Database\Eloquent\Relations\MorphTo',
            'Illuminate\Database\Eloquent\Relations\MorphOne',
            'Illuminate\Database\Eloquent\Relations\MorphMany',
            'Illuminate\Database\Eloquent\Relations\MorphToMany',
            'Illuminate\Database\Eloquent\Relations\MorphedByMany',
        ];

        foreach ($relationClasses as $relationClass) {
            if (is_a($className, $relationClass, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get relation type from class name.
     */
    private function getRelationType(string $className): ?string
    {
        $classBasename = class_basename($className);

        return match ($classBasename) {
            'BelongsTo', 'HasOne', 'MorphTo', 'MorphOne' => 'single',
            'HasMany', 'BelongsToMany', 'HasOneThrough', 'HasManyThrough', 'MorphMany', 'MorphToMany', 'MorphedByMany' => 'collection',
            default => null
        };
    }

    /**
     * Get related model class from relation.
     */
    private function getRelatedModelClass(object $relation): ?string
    {
        try {
            if (method_exists($relation, 'getRelated')) {
                $relatedModel = $relation->getRelated();

                return get_class($relatedModel);
            }

            if (method_exists($relation, 'getModel')) {
                $relatedModel = $relation->getModel();

                return get_class($relatedModel);
            }
        } catch (Exception $e) {
            // Unable to get related model
        }

        return null;
    }

    /**
     * Build DTO configuration for relation.
     *
     * @return array<string, mixed>
     */
    private function buildRelationConfig(string $relationType, string $relatedModelClass): array
    {
        $relatedModelName = class_basename($relatedModelClass);
        $dtoClassName = $relatedModelName . 'DTO';

        if ($relationType === 'single') {
            return [
                'type' => 'nested',
                'class' => $dtoClassName,
                'nullable' => true,
                'relation_type' => 'single',
                'related_model' => $relatedModelClass,
            ];
        }

        // Collection relation
        return [
            'type' => 'collection',
            'class' => $dtoClassName,
            'nullable' => false,
            'relation_type' => 'collection',
            'related_model' => $relatedModelClass,
        ];
    }

    /**
     * Generate smart validation rules based on field name, type and options.
     *
     * @param array<string, mixed> $config
     */
    private function generateValidationRules(string $fieldName, array $config): string
    {
        $rules = [];
        $type = $config['type'] ?? 'string';
        $nullable = $config['nullable'] ?? true;
        $isStrict = $this->option('validation-strict');

        // Required/nullable rules
        if (!$nullable) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Type-based rules
        switch ($type) {
            case 'string':
                if ($isStrict) {
                    $rules[] = 'string';
                    $rules[] = 'max:255'; // Default max length
                }
                break;

            case 'int':
                $rules[] = 'integer';
                if ($isStrict && str_contains($fieldName, '_id')) {
                    $rules[] = 'min:1'; // IDs should be positive
                }
                break;

            case 'float':
                $rules[] = 'numeric';
                if ($isStrict && preg_match('/price|amount|cost/', $fieldName)) {
                    $rules[] = 'min:0'; // Prices should be non-negative
                }
                break;

            case 'bool':
                $rules[] = 'boolean';
                break;

            case 'array':
                $rules[] = 'array';
                break;

            case 'date':
                $rules[] = 'date';
                if ($isStrict) {
                    if (str_contains($fieldName, 'birth')) {
                        $rules[] = 'before:today'; // Birth dates should be in the past
                    } elseif (str_contains($fieldName, 'expir')) {
                        $rules[] = 'after:today'; // Expiration dates should be in the future
                    }
                }
                break;
        }

        // Field name-based rules
        $nameRules = $this->getFieldNameBasedRules($fieldName, $isStrict);
        $rules = array_merge($rules, $nameRules);

        return implode('|', array_unique($rules));
    }

    /**
     * Get validation rules based on field name patterns.
     *
     * @return array<string>
     */
    private function getFieldNameBasedRules(string $fieldName, bool $isStrict): array
    {
        $rules = [];

        // Email validation
        if (str_contains($fieldName, 'email')) {
            $rules[] = 'email';
            if ($isStrict) {
                $rules[] = 'max:254'; // RFC 5321 limit
            }
        }

        // URL validation
        if (str_contains($fieldName, 'url') || str_contains($fieldName, 'website') || str_contains($fieldName, 'link')) {
            $rules[] = 'url';
            if ($isStrict) {
                $rules[] = 'max:2048'; // Reasonable URL length limit
            }
        }

        // Phone validation
        if (str_contains($fieldName, 'phone') || str_contains($fieldName, 'mobile') || str_contains($fieldName, 'tel')) {
            if ($isStrict) {
                $rules[] = 'regex:/^[+]?[0-9\s\-\(\)]{7,20}$/'; // Basic phone format
            }
        }

        // Password validation
        if (str_contains($fieldName, 'password')) {
            $rules[] = 'min:8';
            if ($isStrict) {
                $rules[] = 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'; // Strong password
            }
        }

        // Name validation
        if (preg_match('/(first_name|last_name|full_name|name)$/', $fieldName)) {
            if ($isStrict) {
                $rules[] = 'min:2';
                $rules[] = 'max:50';
                $rules[] = 'regex:/^[a-zA-Z\s\-\']+$/'; // Letters, spaces, hyphens, apostrophes only
            }
        }

        // Postal/ZIP code validation
        if (str_contains($fieldName, 'zip') || str_contains($fieldName, 'postal') || str_contains($fieldName, 'postcode')) {
            if ($isStrict) {
                $rules[] = 'regex:/^[A-Za-z0-9\s\-]{3,10}$/'; // Basic postal code format
            }
        }

        // UUID validation
        if (str_contains($fieldName, 'uuid') || str_ends_with($fieldName, '_uuid')) {
            $rules[] = 'uuid';
        }

        // IP address validation
        if (str_contains($fieldName, 'ip') && !str_contains($fieldName, 'zip')) {
            $rules[] = 'ip';
        }

        // Age validation
        if ($fieldName === 'age') {
            $rules[] = 'min:0';
            if ($isStrict) {
                $rules[] = 'max:150'; // Reasonable age limit
            }
        }

        // Status fields
        if (str_contains($fieldName, 'status')) {
            if ($isStrict) {
                $rules[] = 'in:active,inactive,pending,approved,rejected'; // Common status values
            }
        }

        // Country code validation
        if (str_contains($fieldName, 'country_code') || $fieldName === 'country') {
            if ($isStrict) {
                $rules[] = 'size:2'; // ISO country codes are 2 characters
                $rules[] = 'alpha'; // Country codes are alphabetic
            }
        }

        // Language code validation
        if (str_contains($fieldName, 'language') || str_contains($fieldName, 'locale')) {
            if ($isStrict) {
                $rules[] = 'regex:/^[a-z]{2}([_\-][A-Z]{2})?$/'; // e.g., en, en_US, en-GB
            }
        }

        return $rules;
    }

    /**
     * Check if we have a database connection available.
     */
    private function hasDatabaseConnection(): bool
    {
        try {
            if (!class_exists('\Illuminate\Support\Facades\DB')) {
                return false;
            }

            // Try to get the default connection
            DB::connection()->getPdo();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if Eloquent connection resolver is available.
     */
    private function hasEloquentConnectionResolver(): bool
    {
        try {
            if (!class_exists('\Illuminate\Database\Eloquent\Model')) {
                return false;
            }

            // Try to access the connection resolver statically
            $resolverProperty = new ReflectionProperty('\Illuminate\Database\Eloquent\Model', 'resolver');
            $resolverProperty->setAccessible(true);
            $resolver = $resolverProperty->getValue();

            return $resolver !== null;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Guess related model class from method name.
     */
    private function guessRelatedModelFromMethodName(string $methodName): string
    {
        // Convert method name to potential model name
        // e.g., 'posts' -> 'Post', 'comments' -> 'Comment', 'author' -> 'Author'
        $modelName = Str::studly(Str::singular($methodName));

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

        // Return a generic class name that can be adjusted later
        return "App\\Models\\{$modelName}";
    }

    /**
     * Check if we can safely instantiate a model.
     */
    private function canInstantiateModel(string $modelClass): bool
    {
        try {
            // Check if this is a test model (doesn't need real DB connection)
            if (str_contains($modelClass, 'Test') || str_contains($modelClass, 'Mock')) {
                // Test models should work fine
                $testModel = new $modelClass();
                // Just try accessing a basic property to ensure it works
                $testModel->getFillable();

                return true;
            }

            // For real models, check if we have database infrastructure
            if (!class_exists('\\Illuminate\\Support\\Facades\\DB')) {
                return false;
            }

            // Check if Eloquent's connection resolver is available
            // This is crucial - without this, model instantiation will fail
            if (!class_exists('\\Illuminate\\Database\\Eloquent\\Model')) {
                return false;
            }

            // Try to access the connection resolver statically
            // If this fails, it means we're in a context where Eloquent isn't properly initialized
            $resolverProperty = new ReflectionProperty('\\Illuminate\\Database\\Eloquent\\Model', 'resolver');
            $resolverProperty->setAccessible(true);
            $resolver = $resolverProperty->getValue();

            if ($resolver === null) {
                // No connection resolver available - can't safely instantiate models
                return false;
            }

            // Try to get the default connection and test it
            $connection = DB::connection();
            $pdo = $connection->getPdo();

            // Try a simple query to ensure the connection works
            $connection->select('SELECT 1');

            // Test model instantiation
            $testModel = new $modelClass();
            $testModel->getTable();
            $testModel->getFillable();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Extract properties without instantiating the model.
     *
     * @return array<string, array<string, mixed>>
     */
    private function extractPropertiesWithoutModel(string $modelClass): array
    {
        $properties = [];

        try {
            $reflection = new ReflectionClass($modelClass);

            // Try to get fillable property statically
            if ($reflection->hasProperty('fillable')) {
                $fillableProperty = $reflection->getProperty('fillable');
                $fillableProperty->setAccessible(true);
                $fillable = $fillableProperty->getDefaultValue() ?? [];

                foreach ($fillable as $attribute) {
                    $properties[$attribute] = $this->guessTypeFromPattern($attribute);
                }
            }

            // Add common timestamp fields
            $properties['id'] = ['type' => 'int', 'nullable' => false];
            $properties['created_at'] = ['type' => 'date', 'nullable' => true];
            $properties['updated_at'] = ['type' => 'date', 'nullable' => true];
        } catch (Exception $e) {
            // Fallback to basic properties
            $properties['id'] = ['type' => 'int', 'nullable' => false];
            $properties['created_at'] = ['type' => 'date', 'nullable' => true];
            $properties['updated_at'] = ['type' => 'date', 'nullable' => true];
        }

        return $properties;
    }
}
