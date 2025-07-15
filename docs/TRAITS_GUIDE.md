# Laravel Arc Traits Guide

<div align="center">
  <p><strong>Comprehensive guide to Laravel Arc's built-in traits</strong></p>
  <p><strong>Guide complet des traits intégrés de Laravel Arc</strong></p>
</div>

Laravel Arc DTOs are powered by two types of traits:

1. **Functional Traits** - Three essential traits automatically included in every DTO that provide validation, data conversion, and utility functionality
2. **Behavioral Traits** - Optional traits that add specific fields and methods to DTOs (HasTimestamps, HasUuid, HasSoftDeletes, etc.)

This guide covers all the methods available and how to use them effectively.

*Les DTOs Laravel Arc sont alimentés par deux types de traits :*

1. **Traits fonctionnels** - Trois traits essentiels automatiquement inclus dans chaque DTO qui fournissent des fonctionnalités de validation, de conversion de données et d'utilitaires
2. **Traits comportementaux** - Traits optionnels qui ajoutent des champs et méthodes spécifiques aux DTOs (HasTimestamps, HasUuid, HasSoftDeletes, etc.)

## 📋 Table of Contents / Table des matières

- [Overview / Vue d'ensemble](#overview)
- [Functional Traits (Automatic) / Traits fonctionnels (automatiques)](#functional-traits)
  - [ValidatesData Trait](#validatesdata-trait)
  - [ConvertsData Trait](#convertsdata-trait)
  - [DtoUtilities Trait](#dtoutilities-trait)
- [Behavioral Traits (Optional) / Traits comportementaux (optionnels)](#behavioral-traits)
  - [HasTimestamps Trait](#hastimestamps-trait)
  - [HasUuid Trait](#hasuuid-trait)
  - [HasSoftDeletes Trait](#hassoftdeletes-trait)
  - [HasVersioning Trait](#hasversioning-trait)
  - [HasTagging Trait](#hastagging-trait)
  - [HasAuditing Trait](#hasauditing-trait)
  - [HasCaching Trait](#hascaching-trait)
- [French Documentation / Documentation française](#documentation-française)
- [Best Practices](#best-practices)
- [Examples](#examples)

## Overview

Laravel Arc uses a two-tier trait system:

### Functional Traits (Automatic)
Every generated DTO automatically includes these three functional traits:

```php
<?php

namespace App\DTO;

use Grazulex\LaravelArc\Support\Traits\ConvertsData;
use Grazulex\LaravelArc\Support\Traits\DtoUtilities;
use Grazulex\LaravelArc\Support\Traits\ValidatesData;

final class UserDTO
{
    use ConvertsData;
    use DtoUtilities;
    use ValidatesData;

    // Your DTO properties and methods...
}
```

### Behavioral Traits (Optional)
You can optionally include behavioral traits that add specific fields and methods:

```yaml
# In your YAML definition
header:
  dto: UserDTO
  model: App\Models\User
  namespace: App\DTO
  traits:
    - HasTimestamps
    - HasUuid
    - HasSoftDeletes
```

**Available Behavioral Traits:**
- `HasTimestamps` - Adds created_at, updated_at fields and methods
- `HasUuid` - Adds id field with UUID type and generation
- `HasSoftDeletes` - Adds deleted_at field and soft delete methods
- `HasVersioning` - Adds version field and versioning methods
- `HasTagging` - Adds tags field and tagging methods
- `HasAuditing` - Adds created_by, updated_by fields and audit methods
- `HasCaching` - Adds caching capabilities and methods

**Benefits:**
- ✅ **No code duplication** - Standard methods are provided by functional traits
- ✅ **Extensibility** - DTOs can still extend other classes
- ✅ **Behavioral composability** - Mix and match behavioral traits as needed
- ✅ **Reusability** - All DTOs benefit from the same utility methods
- ✅ **Maintainability** - Single implementation for standard methods
- ✅ **Testability** - Traits are tested independently

## Functional Traits

These three traits are automatically included in every generated DTO and provide core functionality.

## Documentation française

### Traits intégrés dans Laravel Arc

Chaque DTO généré inclut automatiquement trois traits puissants :

#### 1. **ValidatesData** - Méthodes de validation
```php
// Valide et retourne les données
$validated = UserDTO::validate($data);

// Crée une instance de validator
$validator = UserDTO::validator($data);

// Vérifie si la validation passe
$passes = UserDTO::passes($data);

// Vérifie si la validation échoue
$fails = UserDTO::fails($data);
```

#### 2. **ConvertsData** - Méthodes de conversion
```php
// Convertit une collection de modèles
$userDtos = UserDTO::fromModels($models);
// OR using collection method
$userDtos = UserDTO::collection($models);

// Convertit en JSON
$json = $userDto->toJson($options);

// Convertit en Collection
$collection = $userDto->toCollection();

// Filtre les clés spécifiées
$filtered = $userDto->only($keys);

// Exclut les clés spécifiées
$excluded = $userDto->except($keys);
```

#### 3. **DtoUtilities** - Méthodes utilitaires
```php
// Récupère les noms des propriétés
$properties = $userDto->getProperties();

// Vérifie l'existence d'une propriété
$hasProperty = $userDto->hasProperty($property);

// Récupère la valeur d'une propriété
$value = $userDto->getProperty($property);

// Crée une nouvelle instance avec des propriétés modifiées
$modified = $userDto->with($properties);

// Compare deux DTOs
$equal = $userDto->equals($other);
```

### Avantages de cette approche

- ✅ **Pas de redondance** - Les méthodes standard sont dans les traits
- ✅ **Extensibilité** - L'utilisateur peut toujours étendre le DTO avec d'autres classes
- ✅ **Réutilisabilité** - Tous les DTOs bénéficient des mêmes méthodes utilitaires
- ✅ **Maintenabilité** - Une seule implémentation à maintenir pour les méthodes standard
- ✅ **Testabilité** - Les traits sont testés indépendamment

### Exemple complet

```php
<?php

namespace App\DTO;

use App\Models\User;
use Carbon\Carbon;
use Grazulex\LaravelArc\Support\Traits\ConvertsData;
use Grazulex\LaravelArc\Support\Traits\DtoUtilities;
use Grazulex\LaravelArc\Support\Traits\ValidatesData;

final class UserDTO
{
    use ConvertsData;
    use DtoUtilities;
    use ValidatesData;

    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly Carbon $created_at,
        public readonly ?Carbon $updated_at = null,
    ) {}

    public static function fromModel(User $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            created_at: $model->created_at,
            updated_at: $model->updated_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public static function rules(): array
    {
        return [
            'id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'created_at' => ['required', 'date'],
        ];
    }

    // La méthode validate() est fournie par le trait ValidatesData
    // Plus besoin de la dupliquer dans chaque DTO !
}
```

## ValidatesData Trait

The `ValidatesData` trait provides powerful validation methods for your DTOs.

### Methods

#### `validate(array $data): array`
Validates the given data against the DTO's validation rules and returns validated data.

```php
use App\DTO\UserDTO;

try {
    $validated = UserDTO::validate($request->all());
    // Use validated data
} catch (\Illuminate\Validation\ValidationException $e) {
    // Handle validation errors
    return response()->json(['errors' => $e->errors()], 422);
}
```

#### `validator(array $data): \Illuminate\Contracts\Validation\Validator`
Creates a validator instance for the given data.

```php
use App\DTO\UserDTO;

$validator = UserDTO::validator($request->all());

if ($validator->fails()) {
    return response()->json(['errors' => $validator->errors()], 422);
}

$validated = $validator->validated();
```

#### `passes(array $data): bool`
Returns `true` if the data passes validation, `false` otherwise.

```php
use App\DTO\UserDTO;

if (UserDTO::passes($request->all())) {
    // Validation passed
    $user = User::create($request->all());
} else {
    // Validation failed
    return response()->json(['error' => 'Invalid data'], 422);
}
```

#### `fails(array $data): bool`
Returns `true` if the data fails validation, `false` otherwise.

```php
use App\DTO\UserDTO;

if (UserDTO::fails($request->all())) {
    // Validation failed
    $errors = UserDTO::validator($request->all())->errors();
    return response()->json(['errors' => $errors], 422);
}

// Validation passed
$validated = UserDTO::validate($request->all());
```

### Usage Examples

#### Controller Validation
```php
<?php

namespace App\Http\Controllers;

use App\DTO\UserDTO;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Quick validation check
        if (UserDTO::fails($request->all())) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => UserDTO::validator($request->all())->errors()
            ], 422);
        }

        $validated = UserDTO::validate($request->all());
        $user = User::create($validated);

        return response()->json(
            UserDTO::fromModel($user)->toArray(),
            201
        );
    }
}
```

#### Service Validation
```php
<?php

namespace App\Services;

use App\DTO\UserDTO;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function createUser(array $data): UserDTO
    {
        // Validate and throw exception if fails
        $validated = UserDTO::validate($data);
        
        $user = User::create($validated);
        
        return UserDTO::fromModel($user);
    }

    public function validateUserData(array $data): array
    {
        return [
            'passes' => UserDTO::passes($data),
            'fails' => UserDTO::fails($data),
            'errors' => UserDTO::validator($data)->errors()->toArray(),
        ];
    }
}
```

## ConvertsData Trait

The `ConvertsData` trait provides methods for converting data between different formats.

### Methods

#### `fromModels(iterable $models): \Illuminate\Support\Collection`
Converts a collection of models to a collection of DTOs.

#### `collection(iterable $models): \Illuminate\Support\Collection`
Alias for `fromModels()` - provides more intuitive syntax for converting models to DTOs.

```php
use App\DTO\UserDTO;
use App\Models\User;

$users = User::all();
$userDtos = UserDTO::fromModels($users);
// OR
$userDtos = UserDTO::collection($users);

// $userDtos is a Collection of UserDTO instances
foreach ($userDtos as $userDto) {
    echo $userDto->name;
}
```

#### Collection Export Methods

Laravel Arc also provides static methods for exporting collections directly:

**`collectionToJson(iterable $models): string`**
```php
$json = UserDTO::collectionToJson($users);
// Returns: {"data":[{"id":123,"name":"John Doe","email":"john@example.com"}]}
```

**`collectionToYaml(iterable $models): string`**
```php
$yaml = UserDTO::collectionToYaml($users);
// Returns YAML with data wrapper
```

**`collectionToCsv(iterable $models, string $delimiter = ',', string $enclosure = '"', string $escape = '\\', bool $includeHeaders = true): string`**
```php
$csv = UserDTO::collectionToCsv($users);
// Returns CSV with headers
```

**`collectionToXml(iterable $models, string $rootElement = 'collection', string $itemElement = 'item', string $encoding = 'UTF-8'): string`**
```php
$xml = UserDTO::collectionToXml($users, 'users', 'user');
// Returns XML with custom root and item elements
```

**`collectionToMarkdownTable(iterable $models, bool $includeHeaders = true): string`**
```php
$markdown = UserDTO::collectionToMarkdownTable($users);
// Returns markdown table with all users
```

#### `toJson(int $options = 0): string`
Converts the DTO to JSON string.

```php
use App\DTO\UserDTO;
use App\Models\User;

$user = User::first();
$userDto = UserDTO::fromModel($user);

$json = $userDto->toJson();
// Returns: {"id":"123","name":"John Doe","email":"john@example.com"}

$prettyJson = $userDto->toJson(JSON_PRETTY_PRINT);
// Returns formatted JSON
```

#### `toCollection(): \Illuminate\Support\Collection`
Converts the DTO to a Laravel Collection.

```php
use App\DTO\UserDTO;
use App\Models\User;

$user = User::first();
$userDto = UserDTO::fromModel($user);

$collection = $userDto->toCollection();
// $collection is a Collection instance with DTO data

$filtered = $collection->filter(fn($value) => !is_null($value));
```

#### `toYaml(): string`
Converts the DTO to YAML format.

```php
$yaml = $userDto->toYaml();
// Returns: 
// id: 123
// name: "John Doe"
// email: "john@example.com"
```

#### `toCsv(string $delimiter = ',', string $enclosure = '"', string $escape = '\\', bool $includeHeaders = true): string`
Converts the DTO to CSV format.

```php
$csv = $userDto->toCsv();
// Returns: id,name,email
//          123,"John Doe",john@example.com

$csvNoHeaders = $userDto->toCsv(includeHeaders: false);
// Returns: 123,"John Doe",john@example.com
```

#### `toXml(string $rootElement = 'dto', string $encoding = 'UTF-8'): string`
Converts the DTO to XML format.

```php
$xml = $userDto->toXml();
// Returns: <?xml version="1.0" encoding="UTF-8"?>
//          <dto>
//            <id>123</id>
//            <name>John Doe</name>
//            <email>john@example.com</email>
//          </dto>

$customXml = $userDto->toXml('user');
// Uses 'user' as root element instead of 'dto'
```

#### `toToml(): string`
Converts the DTO to TOML format.

```php
$toml = $userDto->toToml();
// Returns: id = 123
//          name = "John Doe"
//          email = "john@example.com"
```

#### `toMarkdownTable(bool $includeHeaders = true): string`
Converts the DTO to Markdown table format.

```php
$markdown = $userDto->toMarkdownTable();
// Returns: | id | name | email |
//          | --- | --- | --- |
//          | 123 | John Doe | john@example.com |
```

#### `toPhpArray(): string`
Converts the DTO to PHP array export format.

```php
$phpArray = $userDto->toPhpArray();
// Returns: array (
//   'id' => 123,
//   'name' => 'John Doe',
//   'email' => 'john@example.com',
// )
```

#### `toQueryString(): string`
Converts the DTO to query string format.

```php
$queryString = $userDto->toQueryString();
// Returns: id=123&name=John+Doe&email=john%40example.com
```

#### `toMessagePack(): string`
Converts the DTO to MessagePack binary format (requires msgpack extension).

```php
try {
    $messagepack = $userDto->toMessagePack();
    // Returns: binary data
} catch (\RuntimeException $e) {
    // MessagePack extension not available
}
```

#### `only(array $keys): array`
Returns only the specified keys from the DTO.

```php
use App\DTO\UserDTO;
use App\Models\User;

$user = User::first();
$userDto = UserDTO::fromModel($user);

$publicData = $userDto->only(['name', 'email']);
// Returns: ['name' => 'John Doe', 'email' => 'john@example.com']
```

#### `except(array $keys): array`
Returns all keys except the specified ones from the DTO.

```php
use App\DTO\UserDTO;
use App\Models\User;

$user = User::first();
$userDto = UserDTO::fromModel($user);

$safeData = $userDto->except(['password', 'remember_token']);
// Returns all data except password and remember_token
```

### Usage Examples

#### API Response Conversion
```php
<?php

namespace App\Http\Controllers;

use App\DTO\UserDTO;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::all();
        $userDtos = UserDTO::collection($users); // Using collection() method

        return response()->json([
            'data' => $userDtos->map(fn($dto) => $dto->toArray()),
            'count' => $userDtos->count(),
        ]);
    }

    public function show(User $user): JsonResponse
    {
        $userDto = UserDTO::fromModel($user);

        return response()->json([
            'user' => $userDto->toArray(),
            'public_data' => $userDto->only(['name', 'email']),
            'json_string' => $userDto->toJson(),
        ]);
    }
}
```

#### Job Processing
```php
<?php

namespace App\Jobs;

use App\DTO\UserDTO;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly array $userIds
    ) {}

    public function handle(): void
    {
        $users = User::whereIn('id', $this->userIds)->get();
        $userDtos = UserDTO::fromModels($users);

        foreach ($userDtos as $userDto) {
            // Process each user
            $this->processUser($userDto);
            
            // Log as JSON
            \Log::info('Processing user', ['user' => $userDto->toJson()]);
        }
    }

    private function processUser(UserDTO $userDto): void
    {
        // Use only essential data for processing
        $essentialData = $userDto->only(['id', 'name', 'email']);
        
        // Process...
    }
}
```

## Advanced Options Generated Methods

When using advanced options in your YAML configuration, Laravel Arc generates additional methods in your DTOs. These methods provide specialized functionality based on the options you enable.

### UUID Option Methods (`uuid: true`)

#### `generateUuid(): string`
Generates a new UUID string.

```php
$uuid = UserDTO::generateUuid();
// Returns: "550e8400-e29b-41d4-a716-446655440000"
```

#### `withGeneratedUuid(array $data = []): static`
Creates a new instance with a generated UUID.

```php
$userDto = UserDTO::withGeneratedUuid([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
// Returns: UserDTO instance with auto-generated UUID
```

### Versioning Option Methods (`versioning: true`)

#### `nextVersion(): static`
Creates a new version with incremented version number.

```php
$v1 = $userDto; // version: 1
$v2 = $userDto->nextVersion(); // version: 2
```

#### `isNewerThan(self $other): bool`
Checks if this DTO version is newer than another.

```php
$isNewer = $v2->isNewerThan($v1); // true
```

#### `getVersionInfo(): array`
Returns version information.

```php
$info = $userDto->getVersionInfo();
// Returns: ['version' => 1, 'is_latest' => true]
```

### Taggable Option Methods (`taggable: true`)

#### `addTag(string $tag): static`
Adds a tag to the DTO.

```php
$taggedDto = $userDto->addTag('featured');
```

#### `removeTag(string $tag): static`
Removes a tag from the DTO.

```php
$untaggedDto = $userDto->removeTag('featured');
```

#### `hasTag(string $tag): bool`
Checks if the DTO has a specific tag.

```php
$hasTag = $userDto->hasTag('featured'); // true/false
```

#### `getTags(): array`
Returns all tags.

```php
$tags = $userDto->getTags(); // ['featured', 'premium']
```

#### `withTag(array $dtos, string $tag): array`
Static method to filter DTOs by tag.

```php
$featuredDtos = UserDTO::withTag($allDtos, 'featured');
```

### Immutable Option Methods (`immutable: true`)

#### `copy(): static`
Creates a copy of the DTO.

```php
$copy = $userDto->copy();
```

#### `equals(self $other): bool`
Compares two DTOs for equality.

```php
$isEqual = $userDto->equals($otherDto); // true/false
```

#### `hash(): string`
Returns a hash of the DTO for caching or comparison.

```php
$hash = $userDto->hash(); // SHA256 hash
```

### Auditable Option Methods (`auditable: true`)

#### `createAuditTrail(string $action, ?string $userId = null): array`
Creates an audit trail entry.

```php
$audit = $userDto->createAuditTrail('updated', $userId);
// Returns: ['action' => 'updated', 'user_id' => '...', 'timestamp' => '...', 'changes' => [...]]
```

#### `setCreator(string $userId): static`
Sets the creator user ID.

```php
$createdDto = $userDto->setCreator($userId);
```

#### `setUpdater(string $userId): static`
Sets the updater user ID.

```php
$updatedDto = $userDto->setUpdater($userId);
```

#### `getAuditInfo(): array`
Returns audit information.

```php
$auditInfo = $userDto->getAuditInfo();
// Returns: ['created_by' => '...', 'updated_by' => '...', 'created_at' => '...', 'updated_at' => '...']
```

### Cacheable Option Methods (`cacheable: true`)

#### `getCacheKey(): string`
Returns the cache key for this DTO.

```php
$key = $userDto->getCacheKey(); // "dto:App\DTO\UserDTO:hash"
```

#### `cache(int $ttl = 3600): static`
Caches the DTO with specified TTL.

```php
$userDto->cache(3600); // Cache for 1 hour
```

#### `fromCache(string $cacheKey): ?static`
Retrieves DTO from cache.

```php
$cachedDto = UserDTO::fromCache($key);
```

#### `clearCache(): bool`
Removes the DTO from cache.

```php
$cleared = $userDto->clearCache(); // true/false
```

#### `isCached(): bool`
Checks if the DTO is cached.

```php
$isCached = $userDto->isCached(); // true/false
```

#### `getCacheMetadata(): array`
Returns cache metadata.

```php
$metadata = $userDto->getCacheMetadata();
// Returns: ['key' => '...', 'exists' => true, 'size' => 1024]
```

### Sluggable Option Methods (`sluggable: {from: fieldname}`)

#### `generateSlug(): static`
Generates a slug from the source field.

```php
$sluggedDto = $userDto->generateSlug();
```

#### `updateSlug(): static`
Updates the slug when the source field changes.

```php
$updatedDto = $userDto->updateSlug();
```

#### `getSlug(): string`
Returns the slug (auto-generates if not set).

```php
$slug = $userDto->getSlug(); // "john-doe"
```

#### `hasUniqueSlug(): bool`
Checks if the slug is unique (basic check).

```php
$isUnique = $userDto->hasUniqueSlug(); // true/false
```

### Combined Usage Example

```php
use App\DTO\UserDTO;

// Create DTO with UUID and tags
$userDto = UserDTO::withGeneratedUuid([
    'name' => 'John Doe',
    'email' => 'john@example.com'
])
->generateSlug()
->addTag('premium')
->addTag('verified')
->setCreator($currentUserId)
->cache(3600);

// Use versioning
$v2 = $userDto->nextVersion();
$isNewer = $v2->isNewerThan($userDto); // true

// Check features
$hasTag = $userDto->hasTag('premium'); // true
$slug = $userDto->getSlug(); // "john-doe"
$isCached = $userDto->isCached(); // true

// Create audit trail
$audit = $userDto->createAuditTrail('created', $currentUserId);
```

## Behavioral Traits

These traits can be optionally included in your DTOs by specifying them in the YAML `header.traits` array.

### HasTimestamps Trait

Adds timestamp fields and methods to DTOs.

**Auto-Generated Fields:**
- `created_at` (datetime, nullable)
- `updated_at` (datetime, nullable)

**Available Methods:**

#### `touch(): static`
Updates the updated_at timestamp to the current time.

```php
$touchedDto = $userDto->touch();
```

#### `wasRecentlyCreated(): bool`
Returns true if the DTO was created less than 1 minute ago.

```php
$isRecent = $userDto->wasRecentlyCreated(); // true/false
```

#### `getAge(): \Carbon\CarbonInterval`
Returns the age of the DTO as a CarbonInterval.

```php
$age = $userDto->getAge(); // CarbonInterval object
```

### HasUuid Trait

Adds UUID field and generation capabilities.

**Auto-Generated Fields:**
- `id` (UUID type, required)

**Features:**
- Automatic UUID validation
- UUID generation methods
- Required field validation

### HasSoftDeletes Trait

Adds soft deletion support to DTOs.

**Auto-Generated Fields:**
- `deleted_at` (datetime, nullable)

**Features:**
- Soft delete timestamp tracking
- Soft delete validation rules

### HasVersioning Trait

Adds version control capabilities.

**Auto-Generated Fields:**
- `version` (integer)

**Available Methods:**

#### `nextVersion(): static`
Creates a new version with incremented version number.

```php
$v1 = $userDto; // version: 1
$v2 = $userDto->nextVersion(); // version: 2
```

#### `isNewerThan(self $other): bool`
Checks if this DTO version is newer than another.

```php
$isNewer = $v2->isNewerThan($v1); // true
```

#### `getVersionInfo(): array`
Returns version information.

```php
$info = $userDto->getVersionInfo();
// Returns: ['version' => 1, 'is_latest' => true]
```

### HasTagging Trait

Adds tagging functionality to DTOs.

**Auto-Generated Fields:**
- `tags` (array)

**Available Methods:**

#### `addTag(string $tag): static`
Adds a tag to the DTO.

```php
$taggedDto = $userDto->addTag('featured');
```

#### `removeTag(string $tag): static`
Removes a tag from the DTO.

```php
$untaggedDto = $userDto->removeTag('featured');
```

#### `hasTag(string $tag): bool`
Checks if the DTO has a specific tag.

```php
$hasTag = $userDto->hasTag('featured'); // true/false
```

#### `getTags(): array`
Returns all tags.

```php
$tags = $userDto->getTags(); // ['featured', 'premium']
```

#### `withTag(array $dtos, string $tag): array`
Static method to filter DTOs by tag.

```php
$featuredDtos = UserDTO::withTag($allDtos, 'featured');
```

### HasAuditing Trait

Adds audit trail support to DTOs.

**Auto-Generated Fields:**
- `created_by` (string, nullable)
- `updated_by` (string, nullable)

**Available Methods:**

#### `createAuditTrail(string $action, ?string $userId = null): array`
Creates an audit trail entry.

```php
$audit = $userDto->createAuditTrail('updated', $userId);
// Returns: ['action' => 'updated', 'user_id' => '...', 'timestamp' => '...', 'changes' => [...]]
```

#### `setCreator(string $userId): static`
Sets the creator user ID.

```php
$createdDto = $userDto->setCreator($userId);
```

#### `setUpdater(string $userId): static`
Sets the updater user ID.

```php
$updatedDto = $userDto->setUpdater($userId);
```

#### `getAuditInfo(): array`
Returns audit information.

```php
$auditInfo = $userDto->getAuditInfo();
// Returns: ['created_by' => '...', 'updated_by' => '...', 'created_at' => '...', 'updated_at' => '...']
```

### HasCaching Trait

Adds caching capabilities to DTOs.

**Auto-Generated Fields:**
- Cache-related metadata

**Available Methods:**

#### `getCacheKey(): string`
Returns the cache key for this DTO.

```php
$key = $userDto->getCacheKey(); // "dto:App\DTO\UserDTO:hash"
```

#### `cache(int $ttl = 3600): static`
Caches the DTO with specified TTL.

```php
$userDto->cache(3600); // Cache for 1 hour
```

#### `fromCache(string $cacheKey): ?static`
Retrieves DTO from cache.

```php
$cachedDto = UserDTO::fromCache($key);
```

#### `clearCache(): bool`
Removes the DTO from cache.

```php
$cleared = $userDto->clearCache(); // true/false
```

#### `isCached(): bool`
Checks if the DTO is cached.

```php
$isCached = $userDto->isCached(); // true/false
```

#### `getCacheMetadata(): array`
Returns cache metadata.

```php
$metadata = $userDto->getCacheMetadata();
// Returns: ['key' => '...', 'exists' => true, 'size' => 1024]
```

### Combined Behavioral Traits Usage

```yaml
header:
  dto: UserDTO
  model: App\Models\User
  namespace: App\DTO
  traits:
    - HasTimestamps
    - HasUuid
    - HasSoftDeletes
    - HasVersioning
    - HasTagging
    - HasAuditing
    - HasCaching

fields:
  name:
    type: string
    required: true
    validation: [required, string, max:255]
  email:
    type: string
    required: true
    validation: [required, email]
```

```php
use App\DTO\UserDTO;

// Create DTO with all behavioral traits
$userDto = UserDTO::fromArray([
    'name' => 'John Doe',
    'email' => 'john@example.com'
])
->addTag('premium')
->addTag('verified')
->setCreator($currentUserId)
->cache(3600);

// Use versioning
$v2 = $userDto->nextVersion();
$isNewer = $v2->isNewerThan($userDto); // true

// Check features
$hasTag = $userDto->hasTag('premium'); // true
$age = $userDto->getAge(); // CarbonInterval
$isCached = $userDto->isCached(); // true

// Create audit trail
$audit = $userDto->createAuditTrail('created', $currentUserId);
```

## DtoUtilities Trait

The `DtoUtilities` trait provides utility methods for introspecting and manipulating DTOs.

### Methods

#### `getProperties(): array`
Returns all property names of the DTO.

```php
use App\DTO\UserDTO;
use App\Models\User;

$user = User::first();
$userDto = UserDTO::fromModel($user);

$properties = $userDto->getProperties();
// Returns: ['id', 'name', 'email', 'created_at', 'updated_at']
```

#### `hasProperty(string $property): bool`
Checks if the DTO has a specific property.

```php
use App\DTO\UserDTO;
use App\Models\User;

$user = User::first();
$userDto = UserDTO::fromModel($user);

if ($userDto->hasProperty('email')) {
    // DTO has email property
}

if (!$userDto->hasProperty('password')) {
    // DTO doesn't have password property
}
```

#### `getProperty(string $property): mixed`
Gets the value of a property by name.

```php
use App\DTO\UserDTO;
use App\Models\User;

$user = User::first();
$userDto = UserDTO::fromModel($user);

$email = $userDto->getProperty('email');
// Returns the email value

try {
    $nonexistent = $userDto->getProperty('nonexistent');
} catch (\InvalidArgumentException $e) {
    // Property doesn't exist
}
```

#### `with(array $properties): static`
Creates a new instance with modified properties.

```php
use App\DTO\UserDTO;
use App\Models\User;

$user = User::first();
$userDto = UserDTO::fromModel($user);

$updatedDto = $userDto->with([
    'name' => 'Updated Name',
    'email' => 'updated@example.com'
]);

// $userDto is unchanged, $updatedDto has new values
echo $userDto->name; // Original name
echo $updatedDto->name; // Updated Name
```

#### `equals(self $other): bool`
Compares two DTOs for equality.

```php
use App\DTO\UserDTO;
use App\Models\User;

$user1 = User::find(1);
$user2 = User::find(2);

$dto1 = UserDTO::fromModel($user1);
$dto2 = UserDTO::fromModel($user2);
$dto1Copy = UserDTO::fromModel($user1);

$dto1->equals($dto2); // false (different users)
$dto1->equals($dto1Copy); // true (same user data)
```

### Usage Examples

#### Dynamic Property Access
```php
<?php

namespace App\Services;

use App\DTO\UserDTO;
use App\Models\User;

class UserInspectionService
{
    public function inspectUser(UserDTO $userDto): array
    {
        $properties = $userDto->getProperties();
        $inspection = [];

        foreach ($properties as $property) {
            if ($userDto->hasProperty($property)) {
                $value = $userDto->getProperty($property);
                $inspection[$property] = [
                    'value' => $value,
                    'type' => gettype($value),
                    'is_null' => is_null($value),
                ];
            }
        }

        return $inspection;
    }

    public function createVariations(UserDTO $userDto): array
    {
        return [
            'original' => $userDto->toArray(),
            'uppercase_name' => $userDto->with(['name' => strtoupper($userDto->name)])->toArray(),
            'with_timestamp' => $userDto->with(['processed_at' => now()])->toArray(),
            'test_version' => $userDto->with(['email' => 'test@example.com'])->toArray(),
        ];
    }
}
```

#### DTO Comparison and Auditing
```php
<?php

namespace App\Services;

use App\DTO\UserDTO;
use App\Models\User;

class UserAuditService
{
    public function auditUserChanges(User $user, array $newData): array
    {
        $originalDto = UserDTO::fromModel($user);
        $newDto = $originalDto->with($newData);

        $changes = [];
        $properties = $originalDto->getProperties();

        foreach ($properties as $property) {
            if ($originalDto->hasProperty($property) && $newDto->hasProperty($property)) {
                $oldValue = $originalDto->getProperty($property);
                $newValue = $newDto->getProperty($property);

                if ($oldValue !== $newValue) {
                    $changes[$property] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }
        }

        return [
            'has_changes' => !empty($changes),
            'changes' => $changes,
            'dtos_equal' => $originalDto->equals($newDto),
            'original' => $originalDto->toArray(),
            'new' => $newDto->toArray(),
        ];
    }
}
```

## Best Practices

### 1. Use Trait Methods for Validation
```php
// ✅ Good - Use trait methods for validation
if (UserDTO::passes($data)) {
    $user = User::create($data);
}

// ❌ Avoid - Manual validation when trait methods are available
$validator = Validator::make($data, UserDTO::rules());
if ($validator->passes()) {
    $user = User::create($data);
}
```

### 2. Leverage Conversion Methods
```php
// ✅ Good - Use trait methods for conversion
$userDtos = UserDTO::collection($users); // Using collection() method
// OR
$userDtos = UserDTO::fromModels($users);
$json = $userDto->toJson();

// ❌ Avoid - Manual conversion
$userDtos = collect($users)->map(fn($user) => UserDTO::fromModel($user));
$json = json_encode($userDto->toArray());
```

### 3. Use Utility Methods for Introspection
```php
// ✅ Good - Use trait methods for introspection
if ($userDto->hasProperty('email')) {
    $email = $userDto->getProperty('email');
}

// ❌ Avoid - Direct property access without checking
$email = $userDto->email ?? null;
```

### 4. Create Immutable Modifications
```php
// ✅ Good - Use with() for immutable modifications
$updatedDto = $userDto->with(['name' => 'New Name']);

// ❌ Avoid - Creating new instances manually
$updatedDto = new UserDTO(
    id: $userDto->id,
    name: 'New Name',
    email: $userDto->email,
    // ... all other properties
);
```

### 5. Use Filtering Methods for Data Security
```php
// ✅ Good - Use trait methods for filtering
$publicData = $userDto->only(['name', 'email']);
$safeData = $userDto->except(['password', 'remember_token']);

// ❌ Avoid - Manual array filtering
$publicData = array_intersect_key($userDto->toArray(), ['name' => 1, 'email' => 1]);
```

## Examples

### Complete CRUD Service with Traits
```php
<?php

namespace App\Services;

use App\DTO\UserDTO;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function createUser(array $data): UserDTO
    {
        // Use ValidatesData trait
        if (UserDTO::fails($data)) {
            throw new ValidationException(UserDTO::validator($data));
        }

        $validated = UserDTO::validate($data);
        $user = User::create($validated);

        return UserDTO::fromModel($user);
    }

    public function updateUser(User $user, array $data): UserDTO
    {
        // Use ValidatesData trait
        if (UserDTO::fails($data)) {
            throw new ValidationException(UserDTO::validator($data));
        }

        $validated = UserDTO::validate($data);
        $user->update($validated);

        return UserDTO::fromModel($user->refresh());
    }

    public function getUsersAsDto(array $userIds): Collection
    {
        $users = User::whereIn('id', $userIds)->get();
        
        // Use ConvertsData trait
        return UserDTO::fromModels($users);
    }

    public function getUserPublicData(User $user): array
    {
        $userDto = UserDTO::fromModel($user);
        
        // Use ConvertsData trait
        return $userDto->only(['name', 'email']);
    }

    public function compareUsers(User $user1, User $user2): array
    {
        $dto1 = UserDTO::fromModel($user1);
        $dto2 = UserDTO::fromModel($user2);

        // Use DtoUtilities trait
        return [
            'equal' => $dto1->equals($dto2),
            'properties' => $dto1->getProperties(),
            'user1_data' => $dto1->toArray(),
            'user2_data' => $dto2->toArray(),
        ];
    }

    public function createUserVariation(User $user, array $changes): UserDTO
    {
        $originalDto = UserDTO::fromModel($user);
        
        // Use DtoUtilities trait
        return $originalDto->with($changes);
    }
}
```

### Complete Controller with All Traits
```php
<?php

namespace App\Http\Controllers;

use App\DTO\UserDTO;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(): JsonResponse
    {
        $users = User::all();
        
        // Use ConvertsData trait
        $userDtos = UserDTO::fromModels($users);

        return response()->json([
            'data' => $userDtos->map(fn($dto) => $dto->toArray()),
            'count' => $userDtos->count(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        // Use ValidatesData trait
        if (UserDTO::fails($request->all())) {
            return response()->json([
                'errors' => UserDTO::validator($request->all())->errors()
            ], 422);
        }

        $userDto = $this->userService->createUser($request->all());
        
        return response()->json($userDto->toArray(), 201);
    }

    public function show(User $user): JsonResponse
    {
        $userDto = UserDTO::fromModel($user);

        return response()->json([
            'user' => $userDto->toArray(),
            'public_data' => $userDto->only(['name', 'email']), // ConvertsData trait
            'properties' => $userDto->getProperties(), // DtoUtilities trait
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        // Use ValidatesData trait
        if (UserDTO::fails($request->all())) {
            return response()->json([
                'errors' => UserDTO::validator($request->all())->errors()
            ], 422);
        }

        $userDto = $this->userService->updateUser($user, $request->all());
        
        return response()->json($userDto->toArray());
    }

    public function compare(Request $request): JsonResponse
    {
        $user1 = User::findOrFail($request->user1_id);
        $user2 = User::findOrFail($request->user2_id);

        $comparison = $this->userService->compareUsers($user1, $user2);
        
        return response()->json($comparison);
    }
}
```

## See Also

- [DTO Usage Guide](DTO_USAGE_GUIDE.md) - Comprehensive examples using DTOs
- [Getting Started Guide](GETTING_STARTED.md) - Basic setup and usage
- [Field Types Reference](FIELD_TYPES.md) - Available field types
- [Validation Rules](VALIDATION_RULES.md) - Validation configuration
- [CLI Commands](CLI_COMMANDS.md) - Command-line tools

---

<div align="center">
  <p><strong>Ready to leverage the power of Laravel Arc traits?</strong></p>
  <p>Check out the <a href="DTO_USAGE_GUIDE.md">DTO Usage Guide</a> for more practical examples!</p>
</div>