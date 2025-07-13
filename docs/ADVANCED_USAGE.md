# Advanced Usage Guide

This guide covers advanced features and techniques for Laravel Arc, including programmatic generation, custom path resolution, enhanced error handling, and complex use cases.

## Table of Contents

- [Programmatic DTO Generation](#programmatic-dto-generation)
- [Path Resolver for Namespace Organization](#path-resolver-for-namespace-organization)
- [Enhanced Error Handling](#enhanced-error-handling)
- [Advanced Options Configuration](#advanced-options-configuration)
- [Custom Header Statements](#custom-header-statements)
- [Nested DTO Relationships](#nested-dto-relationships)
- [Environment-Specific Configuration](#environment-specific-configuration)
- [Performance Optimization](#performance-optimization)
- [Integration Patterns](#integration-patterns)

## Programmatic DTO Generation

For advanced use cases, you can generate DTOs programmatically using the `DtoGenerator` class:

### Basic Programmatic Generation

```php
use Grazulex\LaravelArc\Generator\DtoGenerator;
use Symfony\Component\Yaml\Yaml;

// Load YAML definition
$definition = Yaml::parseFile('path/to/user.yaml');

// Generate DTO code
$generator = DtoGenerator::make();
$code = $generator->generateFromDefinition($definition);

// Save to file
file_put_contents('app/DTOs/UserDTO.php', $code);
```

### Dynamic DTO Generation

```php
use Grazulex\LaravelArc\Generator\DtoGenerator;

// Create definition programmatically
$definition = [
    'header' => [
        'dto' => 'DynamicUserDTO',
        'table' => 'users',
        'model' => 'App\Models\User',
    ],
    'fields' => [
        'id' => [
            'type' => 'uuid',
            'required' => true,
        ],
        'name' => [
            'type' => 'string',
            'required' => true,
            'rules' => ['min:2', 'max:100'],
        ],
        'email' => [
            'type' => 'string',
            'required' => true,
            'rules' => ['email', 'unique:users'],
        ],
    ],
    'options' => [
        'timestamps' => true,
        'namespace' => 'App\DTOs\Dynamic',
    ],
];

$generator = DtoGenerator::make();
$code = $generator->generateFromDefinition($definition);

// Save to custom location
$outputPath = app_path('DTOs/Dynamic/DynamicUserDTO.php');
file_put_contents($outputPath, $code);
```

### Batch Generation

```php
use Grazulex\LaravelArc\Generator\DtoGenerator;

class BatchDtoGenerator
{
    public function generateAll(array $definitions): array
    {
        $generator = DtoGenerator::make();
        $results = [];

        foreach ($definitions as $filename => $definition) {
            try {
                $code = $generator->generateFromDefinition($definition);
                $outputPath = $this->resolveOutputPath($definition);
                
                file_put_contents($outputPath, $code);
                $results[$filename] = ['success' => true, 'path' => $outputPath];
            } catch (\Exception $e) {
                $results[$filename] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        return $results;
    }

    private function resolveOutputPath(array $definition): string
    {
        $namespace = $definition['options']['namespace'] ?? 'App\DTOs';
        $dtoName = $definition['header']['dto'];
        
        return DtoPathResolver::resolveOutputPath($dtoName, $namespace);
    }
}
```

## Path Resolver for Namespace Organization

Laravel Arc includes a powerful `DtoPathResolver` utility class that centralizes namespace-to-path conversion logic.

### Key Features

- **Bidirectional conversion** between namespaces and file paths
- **Automatic sub-namespace detection** for organized directory structures
- **Namespace validation** with PHP standards compliance
- **Custom namespace support** outside the default configuration

### Basic Usage

```php
use Grazulex\LaravelArc\Support\DtoPathResolver;

// Resolve output path from namespace
$path = DtoPathResolver::resolveOutputPath('UserDTO', 'App\DTOs\Admin');
// Result: /path/to/app/DTOs/Admin/UserDTO.php

// Derive namespace from file path
$namespace = DtoPathResolver::resolveNamespaceFromPath('/path/to/app/DTOs/Admin/UserDTO.php');
// Result: App\DTOs\Admin
```

### Namespace Organization Examples

#### Base Namespace
```php
DtoPathResolver::resolveOutputPath('UserDTO', 'App\DTOs');
// → /path/to/app/DTOs/UserDTO.php
```

#### Sub-namespaces
```php
DtoPathResolver::resolveOutputPath('AdminUserDTO', 'App\DTOs\Admin');
// → /path/to/app/DTOs/Admin/AdminUserDTO.php

DtoPathResolver::resolveOutputPath('ProductDTO', 'App\DTOs\Admin\Catalog\Products');
// → /path/to/app/DTOs/Admin/Catalog/Products/ProductDTO.php
```

#### External Namespaces
```php
DtoPathResolver::resolveOutputPath('CustomDTO', 'Library\External\Data');
// → /path/to/Library/External/Data/CustomDTO.php
```

### Utility Methods

```php
// Validate namespace compatibility
$isValid = DtoPathResolver::isValidNamespace('App\DTOs\Admin');
// Result: true

// Normalize namespace (trim whitespace and backslashes)
$normalized = DtoPathResolver::normalizeNamespace('\App\DTOs\Admin\\');
// Result: App\DTOs\Admin

// Check sub-namespace relationships
$isSubNamespace = DtoPathResolver::isSubNamespaceOf('App\DTOs\Admin', 'App\DTOs');
// Result: true
```

### Advanced Path Resolution

```php
use Grazulex\LaravelArc\Support\DtoPathResolver;

class CustomPathResolver
{
    public function resolveCustomPath(string $dtoName, string $namespace): string
    {
        // Custom logic for special cases
        if (str_starts_with($namespace, 'App\DTOs\Legacy')) {
            return base_path('legacy/dto/' . $dtoName . '.php');
        }
        
        if (str_starts_with($namespace, 'App\DTOs\Generated')) {
            return storage_path('app/generated-dtos/' . $dtoName . '.php');
        }
        
        // Fall back to default resolver
        return DtoPathResolver::resolveOutputPath($dtoName, $namespace);
    }
}
```

## Enhanced Error Handling

Laravel Arc provides comprehensive error handling through the `DtoGenerationException` class.

### Error Categories

#### YAML Parsing Errors
```php
use Grazulex\LaravelArc\Exceptions\DtoGenerationException;

try {
    $generator = DtoGenerator::make();
    $code = $generator->generateFromYamlFile('invalid-syntax.yaml');
} catch (DtoGenerationException $e) {
    if ($e->getCode() === 1001) { // YAML parsing error
        echo "YAML Syntax Error: " . $e->getMessage();
        foreach ($e->getSuggestions() as $suggestion) {
            echo "Suggestion: " . $suggestion . "\n";
        }
    }
}
```

#### Field Configuration Errors
```php
try {
    $generator = DtoGenerator::make();
    $code = $generator->generateFromDefinition($definition);
} catch (DtoGenerationException $e) {
    if ($e->getCode() === 1004) { // Unsupported field type
        echo "Field Error: " . $e->getMessage();
        echo "Field: " . $e->getFieldName();
        echo "DTO: " . $e->getDtoName();
    }
}
```

### Custom Error Handling

```php
class CustomDtoGenerator
{
    public function generateWithCustomErrorHandling(array $definition): string
    {
        try {
            $generator = DtoGenerator::make();
            return $generator->generateFromDefinition($definition);
        } catch (DtoGenerationException $e) {
            // Log detailed error information
            logger()->error('DTO Generation Failed', [
                'error_code' => $e->getCode(),
                'message' => $e->getMessage(),
                'yaml_file' => $e->getYamlFile(),
                'dto_name' => $e->getDtoName(),
                'field_name' => $e->getFieldName(),
                'context' => $e->getContext(),
                'suggestions' => $e->getSuggestions(),
            ]);
            
            // Handle specific error types
            return match ($e->getCode()) {
                1001 => $this->handleYamlParsingError($e),
                1004 => $this->handleFieldTypeError($e),
                1005 => $this->handleNamespaceError($e),
                default => $this->handleGenericError($e),
            };
        }
    }
    
    private function handleYamlParsingError(DtoGenerationException $e): string
    {
        // Provide default DTO structure
        return $this->generateDefaultDto($e->getDtoName());
    }
    
    private function handleFieldTypeError(DtoGenerationException $e): string
    {
        // Replace unknown field types with string
        $definition = $e->getContext();
        $definition['fields'][$e->getFieldName()]['type'] = 'string';
        
        $generator = DtoGenerator::make();
        return $generator->generateFromDefinition($definition);
    }
}
```

## Advanced Options Configuration

Laravel Arc provides powerful advanced options that can be configured in your YAML files to add specialized functionality to your DTOs. These options automatically generate additional methods and fields.

### UUID Option

Enable automatic UUID generation and helper methods:

```yaml
options:
  uuid: true
```

**Generated Features:**
- Adds `id` field with UUID type and validation
- Generates `generateUuid()` static method
- Generates `withGeneratedUuid()` static method

**Usage:**
```php
// Generate UUID
$uuid = UserDTO::generateUuid();

// Create DTO with auto-generated UUID
$userDto = UserDTO::withGeneratedUuid([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

### Versioning Option

Enable version tracking and comparison:

```yaml
options:
  versioning: true
```

**Generated Features:**
- Adds `version` field (integer, default: 1)
- Generates `nextVersion()` method
- Generates `isNewerThan()` method
- Generates `getVersionInfo()` method

**Usage:**
```php
$v1 = $userDto; // version: 1
$v2 = $userDto->nextVersion(); // version: 2
$isNewer = $v2->isNewerThan($v1); // true
```

### Taggable Option

Enable tag management system:

```yaml
options:
  taggable: true
```

**Generated Features:**
- Adds `tags` field (array, default: [])
- Generates tag management methods: `addTag()`, `removeTag()`, `hasTag()`, `getTags()`
- Generates static `withTag()` filtering method

**Usage:**
```php
$taggedDto = $userDto->addTag('premium')->addTag('featured');
$hasTag = $taggedDto->hasTag('premium'); // true
$premiumDtos = UserDTO::withTag($allDtos, 'premium');
```

### Immutable Option

Enable immutable pattern methods:

```yaml
options:
  immutable: true
```

**Generated Features:**
- Generates `with()` method for immutable updates
- Generates `copy()` method for duplication
- Generates `equals()` method for comparison
- Generates `hash()` method for caching

**Usage:**
```php
$newDto = $userDto->with(['name' => 'New Name']);
$copy = $userDto->copy();
$isEqual = $userDto->equals($otherDto);
$hash = $userDto->hash();
```

### Auditable Option

Enable audit trail functionality:

```yaml
options:
  auditable: true
```

**Generated Features:**
- Adds `created_by` and `updated_by` fields (UUID)
- Generates audit methods: `createAuditTrail()`, `setCreator()`, `setUpdater()`, `getAuditInfo()`

**Usage:**
```php
$auditedDto = $userDto->setCreator($userId)->setUpdater($userId);
$auditTrail = $auditedDto->createAuditTrail('updated', $userId);
```

### Cacheable Option

Enable caching capabilities:

```yaml
options:
  cacheable: true
```

**Generated Features:**
- Generates caching methods: `cache()`, `fromCache()`, `clearCache()`, `isCached()`
- Generates cache utilities: `getCacheKey()`, `getCacheMetadata()`

**Usage:**
```php
$userDto->cache(3600); // Cache for 1 hour
$cached = UserDTO::fromCache($userDto->getCacheKey());
$metadata = $userDto->getCacheMetadata();
```

### Sluggable Option

Enable slug generation from specified fields:

```yaml
options:
  sluggable:
    from: name
```

**Generated Features:**
- Adds `slug` field with validation
- Generates slug methods: `generateSlug()`, `updateSlug()`, `getSlug()`, `hasUniqueSlug()`

**Usage:**
```php
$sluggedDto = $userDto->generateSlug();
$slug = $sluggedDto->getSlug(); // "john-doe"
```

### Combined Advanced Options

You can combine multiple advanced options:

```yaml
options:
  # Basic options
  timestamps: true
  soft_deletes: true
  namespace: App\DTOs\Advanced
  
  # Advanced options
  uuid: true
  versioning: true
  taggable: true
  immutable: true
  auditable: true
  cacheable: true
  sluggable:
    from: name
```

**Programmatic Configuration:**
```php
$definition = [
    'header' => [
        'dto' => 'AdvancedUserDTO',
        'model' => 'App\Models\User',
    ],
    'fields' => [
        'name' => ['type' => 'string', 'required' => true],
        'email' => ['type' => 'string', 'required' => true],
    ],
    'options' => [
        'timestamps' => true,
        'uuid' => true,
        'versioning' => true,
        'taggable' => true,
        'immutable' => true,
        'auditable' => true,
        'cacheable' => true,
        'sluggable' => ['from' => 'name'],
        'namespace' => 'App\DTOs\Advanced',
    ],
];

$generator = DtoGenerator::make();
$code = $generator->generateFromDefinition($definition);
```

### Advanced Options Benefits

1. **Automatic Field Generation**: Options automatically add necessary fields
2. **Method Generation**: Comprehensive methods for each feature
3. **Type Safety**: All generated methods are properly typed
4. **Validation**: Automatic validation rules for generated fields
5. **Documentation**: Generated methods are self-documenting
6. **Performance**: Efficient implementations with caching support
7. **Flexibility**: Options can be combined as needed
8. **Standards Compliance**: Follows Laravel and PHP best practices

## Custom Header Statements

Laravel Arc supports custom header statements to enhance generated DTOs.

### Use Statements

```yaml
header:
  dto: UserDTO
  model: App\Models\User
  use:
    - App\Traits\HasUuid
    - Illuminate\Support\Facades\Validator
    - App\Interfaces\AuditableInterface
```

Generated code:
```php
<?php
declare(strict_types=1);

namespace App\DTOs;

use App\Traits\HasUuid;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\AuditableInterface;

final class UserDTO
{
    // ... DTO content
}
```

### Extends Clause

```yaml
header:
  dto: UserDTO
  model: App\Models\User
  extends: BaseDTO
```

Generated code:
```php
<?php
declare(strict_types=1);

namespace App\DTOs;

final class UserDTO extends BaseDTO
{
    // ... DTO content
}
```

### Combined Usage

```yaml
header:
  dto: UserDTO
  model: App\Models\User
  use:
    - App\Traits\HasUuid
    - App\Traits\Auditable
  extends: BaseDTO
```

### Programmatic Header Generation

```php
class HeaderGenerator
{
    public function generateAdvancedHeader(array $requirements): array
    {
        $header = [
            'dto' => $requirements['dto_name'],
            'model' => $requirements['model_class'],
        ];
        
        $useStatements = [];
        
        // Add traits based on requirements
        if ($requirements['has_uuid']) {
            $useStatements[] = 'App\Traits\HasUuid';
        }
        
        if ($requirements['is_auditable']) {
            $useStatements[] = 'App\Traits\Auditable';
        }
        
        if ($requirements['has_validation']) {
            $useStatements[] = 'Illuminate\Support\Facades\Validator';
        }
        
        // Add interfaces
        if ($requirements['implements_cacheable']) {
            $useStatements[] = 'App\Interfaces\CacheableInterface';
        }
        
        if (!empty($useStatements)) {
            $header['use'] = $useStatements;
        }
        
        // Add base class
        if ($requirements['extends_base']) {
            $header['extends'] = $requirements['base_class'] ?? 'BaseDTO';
        }
        
        return $header;
    }
}
```

## Nested DTO Relationships

Laravel Arc supports nested DTOs with advanced protection against circular references.

### Circular Reference Protection

```php
use Grazulex\LaravelArc\Generator\DtoGenerator;

class CircularReferenceHandler
{
    private array $generationStack = [];
    
    public function generateWithCircularProtection(array $definition): string
    {
        $dtoName = $definition['header']['dto'];
        
        // Check for circular reference
        if (in_array($dtoName, $this->generationStack)) {
            throw new \Exception("Circular reference detected: " . implode(' -> ', $this->generationStack) . " -> {$dtoName}");
        }
        
        $this->generationStack[] = $dtoName;
        
        try {
            $generator = DtoGenerator::make();
            $code = $generator->generateFromDefinition($definition);
            
            array_pop($this->generationStack);
            return $code;
        } catch (\Exception $e) {
            array_pop($this->generationStack);
            throw $e;
        }
    }
}
```

### Depth Limiting

```php
class DepthLimitedGenerator
{
    private int $maxDepth = 3;
    private int $currentDepth = 0;
    
    public function generateWithDepthLimit(array $definition): string
    {
        $this->currentDepth++;
        
        if ($this->currentDepth > $this->maxDepth) {
            // Convert nested DTOs to arrays at depth limit
            $definition = $this->convertNestedDtosToArrays($definition);
        }
        
        try {
            $generator = DtoGenerator::make();
            $code = $generator->generateFromDefinition($definition);
            
            $this->currentDepth--;
            return $code;
        } catch (\Exception $e) {
            $this->currentDepth--;
            throw $e;
        }
    }
    
    private function convertNestedDtosToArrays(array $definition): array
    {
        foreach ($definition['fields'] as $fieldName => $fieldConfig) {
            if (($fieldConfig['type'] ?? '') === 'dto') {
                $definition['fields'][$fieldName]['type'] = 'array';
                unset($definition['fields'][$fieldName]['dto']);
            }
        }
        
        return $definition;
    }
}
```

## Environment-Specific Configuration

### Dynamic Configuration

```php
class EnvironmentAwareDtoGenerator
{
    public function generateForEnvironment(array $definition, string $environment): string
    {
        // Modify definition based on environment
        $definition = $this->applyEnvironmentSettings($definition, $environment);
        
        $generator = DtoGenerator::make();
        return $generator->generateFromDefinition($definition);
    }
    
    private function applyEnvironmentSettings(array $definition, string $environment): array
    {
        switch ($environment) {
            case 'testing':
                $definition['options']['namespace'] = 'Tests\DTOs';
                $definition['options']['timestamps'] = false;
                break;
                
            case 'production':
                $definition['options']['soft_deletes'] = true;
                $definition['options']['expose_hidden_by_default'] = false;
                break;
                
            case 'development':
                $definition['options']['timestamps'] = true;
                $definition['options']['soft_deletes'] = false;
                break;
        }
        
        return $definition;
    }
}
```

### Environment-Specific YAML

```yaml
# user.yaml
header:
  dto: UserDTO
  model: App\Models\User

fields:
  id:
    type: uuid
    required: true
  name:
    type: string
    required: true
  email:
    type: string
    required: true
    rules: [email, unique:users]

options:
  timestamps: ${ENVIRONMENT_TIMESTAMPS:true}
  soft_deletes: ${ENVIRONMENT_SOFT_DELETES:false}
  namespace: ${ENVIRONMENT_NAMESPACE:App\DTOs}
```

## Performance Optimization

### Caching Generated Code

```php
class CachedDtoGenerator
{
    private string $cacheKey = 'dto_generation_cache';
    
    public function generateWithCache(array $definition): string
    {
        $definitionHash = md5(serialize($definition));
        $cacheKey = $this->cacheKey . '.' . $definitionHash;
        
        // Check cache
        if (cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }
        
        // Generate and cache
        $generator = DtoGenerator::make();
        $code = $generator->generateFromDefinition($definition);
        
        cache()->put($cacheKey, $code, now()->addHours(24));
        
        return $code;
    }
    
    public function clearCache(): void
    {
        cache()->forget($this->cacheKey);
    }
}
```

### Lazy Loading

```php
class LazyDtoGenerator
{
    private array $definitions = [];
    private array $generatedCode = [];
    
    public function addDefinition(string $name, array $definition): void
    {
        $this->definitions[$name] = $definition;
    }
    
    public function generate(string $name): string
    {
        if (isset($this->generatedCode[$name])) {
            return $this->generatedCode[$name];
        }
        
        if (!isset($this->definitions[$name])) {
            throw new \InvalidArgumentException("Definition '{$name}' not found");
        }
        
        $generator = DtoGenerator::make();
        $code = $generator->generateFromDefinition($this->definitions[$name]);
        
        $this->generatedCode[$name] = $code;
        
        return $code;
    }
}
```

## Integration Patterns

### Service Container Integration

```php
// AppServiceProvider.php
public function register(): void
{
    $this->app->singleton(DtoGenerator::class, function ($app) {
        return DtoGenerator::make();
    });
    
    $this->app->bind('dto.generator', DtoGenerator::class);
}

// Usage in controller
public function generateDto(Request $request)
{
    $generator = app('dto.generator');
    $definition = $request->input('definition');
    
    try {
        $code = $generator->generateFromDefinition($definition);
        return response()->json(['code' => $code]);
    } catch (DtoGenerationException $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
}
```

### Event-Driven Generation

```php
// Events
class DtoGenerationRequested
{
    public function __construct(
        public array $definition,
        public string $outputPath
    ) {}
}

class DtoGenerated
{
    public function __construct(
        public string $dtoName,
        public string $filePath,
        public string $namespace
    ) {}
}

// Listeners
class GenerateDtoListener
{
    public function handle(DtoGenerationRequested $event): void
    {
        $generator = DtoGenerator::make();
        $code = $generator->generateFromDefinition($event->definition);
        
        file_put_contents($event->outputPath, $code);
        
        event(new DtoGenerated(
            $event->definition['header']['dto'],
            $event->outputPath,
            $event->definition['options']['namespace'] ?? 'App\DTOs'
        ));
    }
}
```

### Queue Integration

```php
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateDtoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        private array $definition,
        private string $outputPath
    ) {}
    
    public function handle(): void
    {
        $generator = DtoGenerator::make();
        $code = $generator->generateFromDefinition($this->definition);
        
        file_put_contents($this->outputPath, $code);
        
        // Notify completion
        event(new DtoGenerated(
            $this->definition['header']['dto'],
            $this->outputPath,
            $this->definition['options']['namespace'] ?? 'App\DTOs'
        ));
    }
}

// Usage
GenerateDtoJob::dispatch($definition, $outputPath);
```

## See Also

- [Getting Started Guide](GETTING_STARTED.md)
- [YAML Schema Documentation](YAML_SCHEMA.md)
- [CLI Commands Reference](CLI_COMMANDS.md)
- [Field Types Reference](FIELD_TYPES.md)
- [Path Resolver Guide](DTO_PATH_RESOLVER_GUIDE.md)
- [Error Handling Guide](DTO_GENERATION_EXCEPTION_GUIDE.md)