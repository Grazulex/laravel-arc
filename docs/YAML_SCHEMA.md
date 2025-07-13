# YAML Schema Reference

This document provides a comprehensive reference for Laravel Arc's YAML schema for defining Data Transfer Objects (DTOs).

## File Structure

Every DTO definition file must be a valid YAML file with the following top-level sections:

```yaml
header:
  # Basic DTO configuration
  
fields:
  # Field definitions
  
relations:
  # Eloquent relationship definitions (optional)
```

## Header Section

The header section contains metadata about the DTO being generated.

### Required Attributes

| Attribute | Type | Description | Example |
|-----------|------|-------------|---------|
| `dto` | string | Name of the DTO class to generate | `UserDTO` |
| `model` | string | Associated Eloquent model class | `App\Models\User` |
| `namespace` | string | Namespace for the generated DTO | `App\DTOs` |
| `traits` | array | List of behavioral traits to include | `["HasTimestamps", "HasUuid"]` |

### Optional Attributes

| Attribute | Type | Description | Example |
|-----------|------|-------------|---------|
| `table` | string | Database table name (for reference) | `users` |
| `use` | array/string | Use statements for traits, imports, or other classes | `["App\Traits\CustomTrait", "Validator"]` |
| `extends` | string | Base class that the DTO should extend | `BaseDTO` |

### Example

```yaml
header:
  dto: UserDTO
  table: users
  model: App\Models\User
  namespace: App\DTOs
  traits:
    - HasTimestamps
    - HasUuid
    - HasSoftDeletes
  use:
    - App\Traits\CustomTrait
```
    - Illuminate\Support\Facades\Validator
  extends: BaseDTO
```

## Fields Section

The fields section defines all properties of the DTO. Each field is defined as a key-value pair where the key is the field name and the value is a configuration object.

### Common Field Attributes

| Attribute | Type | Default | Required | Description |
|-----------|------|---------|----------|-------------|
| `type` | string | - | Yes | Field data type (see [Field Types](#field-types)) |
| `required` | boolean | `false` | No | Whether the field is required |
| `default` | mixed | - | No | Default value for the field |
| `rules` | array | `[]` | No | Laravel validation rules |

### Field Types

#### Primitive Types

**String**
```yaml
name:
  type: string
  required: true
  rules: [min:2, max:100]
```

**Integer**
```yaml
age:
  type: integer
  rules: [min:0, max:150]
```

**Float**
```yaml
price:
  type: float
  rules: [numeric, min:0]
```

**Boolean**
```yaml
is_active:
  type: boolean
  default: true
```

**Array**
```yaml
tags:
  type: array
  rules: [array, distinct]
```

**JSON**
```yaml
metadata:
  type: json
```

#### Specialized Types

**UUID**
```yaml
id:
  type: uuid
  required: true
```

**Enum**

Traditional enum with values array:
```yaml
status:
  type: enum
  values: [draft, published, archived]
  default: draft
```

PHP enum class (recommended):
```yaml
# String enum
status:
  type: enum
  class: App\Enums\Status
  default: draft  # References Status::DRAFT

# Int enum
priority:
  type: enum
  class: App\Enums\Priority
  default: 2  # References Priority::MEDIUM (if value is 2)

# Explicit enum case reference
visibility:
  type: enum
  class: App\Enums\Visibility
  default: App\Enums\Visibility::PUBLIC
```

**Enum Field Attributes:**
| Attribute | Type | Required | Description |
|-----------|------|----------|-------------|
| `values` | array | No | Array of allowed values (traditional enum) |
| `class` | string | No | PHP enum class name (PHP enum class) |
| `default` | mixed | No | Default value (case name or full enum reference) |

**Note:** Use either `values` or `class`, not both. PHP enum classes are recommended for better type safety and IDE support.

**ID (Auto-incrementing)**
```yaml
id:
  type: id
  required: true
```

**Text (Long text)**
```yaml
description:
  type: text
  rules: [max:1000]
```

**Decimal**
```yaml
amount:
  type: decimal
  rules: [regex:/^\d+(\.\d{2})?$/]
```

#### Date/Time Types

**DateTime**
```yaml
created_at:
  type: datetime
```

**Date**
```yaml
birth_date:
  type: date
```

**Time**
```yaml
notification_time:
  type: time
```

#### Complex Types

**DTO (Nested DTO)**
```yaml
profile:
  type: dto
  dto: profile  # References profile.yaml definition
```

### Validation Rules

Fields support all Laravel validation rules:

```yaml
fields:
  email:
    type: string
    rules: [required, email, unique:users, max:255]
  
  password:
    type: string
    rules: [required, min:8, confirmed]
  
  age:
    type: integer
    rules: [integer, min:13, max:120]
  
  website:
    type: string
    rules: [url, max:255]
  
  tags:
    type: array
    rules: [array, distinct, max:10]
```

### Default Values

Default values are typed according to the field type:

```yaml
fields:
  # String defaults
  status:
    type: string
    default: "active"
  
  # Numeric defaults
  count:
    type: integer
    default: 0
  
  # Boolean defaults
  is_verified:
    type: boolean
    default: false
  
  # Array defaults
  permissions:
    type: array
    default: []
  
  # Null defaults
  deleted_at:
    type: datetime
    default: null
```

## Relations Section

Define Eloquent relationships that will be included in the DTO.

### Relationship Types

| Type | Description | Example Use Case |
|------|-------------|------------------|
| `belongsTo` | Many-to-one relationship | User belongs to Company |
| `hasOne` | One-to-one relationship | User has one Profile |
| `hasMany` | One-to-many relationship | User has many Posts |
| `belongsToMany` | Many-to-many relationship | User belongs to many Roles |

### Relationship Attributes

| Attribute | Type | Required | Description |
|-----------|------|----------|-------------|
| `type` | string | Yes | Relationship type |
| `target` | string | Yes | Target model class |

### Examples

```yaml
relations:
  # One-to-one relationship
  profile:
    type: hasOne
    target: App\Models\Profile
  
  # One-to-many relationship
  posts:
    type: hasMany
    target: App\Models\Post
  
  # Many-to-one relationship
  company:
    type: belongsTo
    target: App\Models\Company
  
  # Many-to-many relationship
  roles:
    type: belongsToMany
    target: App\Models\Role
```

## Options Section

Configure how the DTO is generated.

### Available Options

#### Basic Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `timestamps` | boolean | `false` | Include `created_at` and `updated_at` fields |
| `soft_deletes` | boolean | `false` | Include `deleted_at` field |
| `expose_hidden_by_default` | boolean | `false` | Expose hidden model attributes |
| `namespace` | string | `App\DTOs` | PHP namespace for the generated DTO |

#### Advanced Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `uuid` | boolean | `false` | Auto-generate UUID `id` field with UUID helper methods |
| `versioning` | boolean | `false` | Add `version` field with versioning methods |
| `taggable` | boolean | `false` | Add `tags` field with tag management methods |
| `immutable` | boolean | `false` | Add immutable helper methods (`with`, `copy`, `equals`, `hash`) |
| `auditable` | boolean | `false` | Add audit fields (`created_by`, `updated_by`) and audit trail methods |
| `cacheable` | boolean | `false` | Add caching methods and cache key generation |
| `sluggable` | object | `null` | Add `slug` field with slug generation methods |

#### Sluggable Configuration

The `sluggable` option accepts an object with configuration:

```yaml
options:
  sluggable:
    from: name  # Source field for slug generation
```

### Basic Example

```yaml
options:
  timestamps: true
  soft_deletes: false
  expose_hidden_by_default: false
  namespace: App\DTOs\Products
```

### Advanced Example

```yaml
options:
  # Basic options
  timestamps: true
  soft_deletes: true
  namespace: App\DTOs\Advanced
  
  # Advanced options
  uuid: true              # Auto-generate UUID field and methods
  versioning: true        # Add version field and versioning methods
  taggable: true         # Add tags field and tag management
  immutable: true        # Add immutable pattern methods
  auditable: true        # Add audit trail functionality
  cacheable: true        # Add caching capabilities
  sluggable:             # Add slug generation
    from: name
```

## Advanced Options Details

### UUID Option (`uuid: true`)

When enabled, automatically adds:
- **Field**: `id` with type `uuid` and required validation
- **Methods**: `generateUuid()`, `withGeneratedUuid()`

```php
// Generated methods
$dto = ProductDTO::withGeneratedUuid(['name' => 'Test Product']);
$uuid = ProductDTO::generateUuid();
```

### Versioning Option (`versioning: true`)

When enabled, automatically adds:
- **Field**: `version` with type `integer`, default `1`
- **Methods**: `nextVersion()`, `isNewerThan()`, `getVersionInfo()`

```php
// Generated methods
$newVersion = $dto->nextVersion();
$isNewer = $dto->isNewerThan($otherDto);
$versionInfo = $dto->getVersionInfo();
```

### Taggable Option (`taggable: true`)

When enabled, automatically adds:
- **Field**: `tags` with type `array`, default `[]`
- **Methods**: `addTag()`, `removeTag()`, `hasTag()`, `getTags()`, `withTag()`

```php
// Generated methods
$dto = $dto->addTag('featured');
$dto = $dto->removeTag('draft');
$hasTag = $dto->hasTag('featured');
$tags = $dto->getTags();
$filteredDtos = ProductDTO::withTag($dtos, 'featured');
```

### Immutable Option (`immutable: true`)

When enabled, adds immutable pattern methods:
- **Methods**: `with()`, `copy()`, `equals()`, `hash()`

```php
// Generated methods
$newDto = $dto->with(['name' => 'New Name']);
$copy = $dto->copy();
$isEqual = $dto->equals($otherDto);
$hash = $dto->hash();
```

### Auditable Option (`auditable: true`)

When enabled, automatically adds:
- **Fields**: `created_by`, `updated_by` with type `uuid`
- **Methods**: `createAuditTrail()`, `setCreator()`, `setUpdater()`, `getAuditInfo()`

```php
// Generated methods
$audit = $dto->createAuditTrail('created', $userId);
$dto = $dto->setCreator($userId);
$dto = $dto->setUpdater($userId);
$auditInfo = $dto->getAuditInfo();
```

### Cacheable Option (`cacheable: true`)

When enabled, adds caching capabilities:
- **Methods**: `getCacheKey()`, `cache()`, `fromCache()`, `clearCache()`, `isCached()`, `getCacheMetadata()`

```php
// Generated methods
$key = $dto->getCacheKey();
$dto->cache(3600); // Cache for 1 hour
$cached = ProductDTO::fromCache($key);
$dto->clearCache();
$isCached = $dto->isCached();
$metadata = $dto->getCacheMetadata();
```

### Sluggable Option (`sluggable: {from: fieldname}`)

When enabled, automatically adds:
- **Field**: `slug` with type `string` and slug validation rules
- **Methods**: `generateSlug()`, `updateSlug()`, `getSlug()`, `hasUniqueSlug()`

```php
// Generated methods
$dto = $dto->generateSlug();
$dto = $dto->updateSlug();
$slug = $dto->getSlug();
$isUnique = $dto->hasUniqueSlug();
```

## Complete Example

Here's a comprehensive example showing all features including advanced options:

```yaml
# database/dto_definitions/product.yaml
header:
  dto: ProductDTO
  table: products
  model: App\Models\Product
  use:
    - App\Traits\CustomTrait
```

## Behavioral Traits System

Laravel Arc now uses a trait-based system for adding behavioral functionality to DTOs. Instead of using the deprecated `options` section, you can now specify traits in the `header.traits` array.

### Available Behavioral Traits

| Trait | Description | Fields Added | Methods Added |
|-------|-------------|--------------|---------------|
| `HasTimestamps` | Adds timestamp fields and methods | `created_at`, `updated_at` | `touch()`, `wasRecentlyCreated()`, `getAge()` |
| `HasUuid` | Adds UUID field and generation | `id` (UUID type) | UUID validation and generation |
| `HasSoftDeletes` | Adds soft deletion support | `deleted_at` | Soft delete methods |
| `HasVersioning` | Adds versioning support | `version` | `nextVersion()`, `isNewerThan()`, `getVersionInfo()` |
| `HasTagging` | Adds tagging functionality | `tags` | `addTag()`, `removeTag()`, `hasTag()`, `getTags()` |
| `HasAuditing` | Adds audit trail support | `created_by`, `updated_by` | `createAuditTrail()`, `setCreator()`, `setUpdater()` |
| `HasCaching` | Adds caching capabilities | Caching metadata | `cache()`, `clearCache()`, `getCacheKey()`, `isCached()` |

### Examples

**Basic traits usage:**
```yaml
header:
  dto: UserDTO
  model: App\Models\User
  namespace: App\DTOs
  traits:
    - HasTimestamps
    - HasUuid

fields:
  name:
    type: string
    validation: [required, string, max:255]
  email:
    type: string
    validation: [required, email]
```

**Advanced traits usage:**
```yaml
header:
  dto: UserDTO
  model: App\Models\User
  namespace: App\DTOs
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
    validation: [required, string, max:255]
  email:
    type: string
    validation: [required, email]
  status:
    type: string
    default: "active"
    validation: [required, in:active,inactive]
```

### Migration from Options

If you have existing DTOs using the old `options` system, here's how to migrate:

**Old format (deprecated):**
```yaml
options:
  timestamps: true
  soft_deletes: true
  uuid: true
  versioning: true
  taggable: true
  auditable: true
  cacheable: true
```

**New format (recommended):**
```yaml
header:
  traits:
    - HasTimestamps
    - HasSoftDeletes
    - HasUuid
    - HasVersioning
    - HasTagging
    - HasAuditing
    - HasCaching
```
    - App\Traits\Auditable
  extends: BaseDTO

fields:
  # Basic fields
  name:
    type: string
    required: true
    rules: [min:2, max:255]
  
  description:
    type: text
    rules: [max:1000]
  
  # Numeric fields
  price:
    type: decimal
    rules: [numeric, min:0]
  
  stock_quantity:
    type: integer
    default: 0
    rules: [integer, min:0]
  
  # Boolean field
  is_active:
    type: boolean
    default: true
  
  # Enum field with PHP enum class
  status:
    type: enum
    class: App\Enums\ProductStatus
    default: draft
  
  # Array field
  tags:
    type: array
    rules: [array, distinct]
  
  # JSON field
  specifications:
    type: json
  
  # Date fields
  published_at:
    type: datetime
  
  # Nested DTO
  category_details:
    type: dto
    dto: category

relations:
  category:
    type: belongsTo
    target: App\Models\Category
  
  reviews:
    type: hasMany
    target: App\Models\Review
  
  tags:
    type: belongsToMany
    target: App\Models\Tag

options:
  # Basic options
  timestamps: true
  soft_deletes: true
  namespace: App\DTOs\Catalog
  
  # Advanced options
  uuid: true              # Auto-generates UUID id field
  versioning: true        # Adds version field and methods
  taggable: true         # Adds tags field and tag methods
  immutable: true        # Adds immutable helper methods
  auditable: true        # Adds audit trail fields and methods
  cacheable: true        # Adds caching capabilities
  sluggable:             # Adds slug field and methods
    from: name
```

This example will generate a DTO with:
- UUID primary key with helper methods
- Version tracking with `nextVersion()` and `isNewerThan()`
- Tag management with `addTag()`, `removeTag()`, `hasTag()`
- Immutable methods like `with()`, `copy()`, `equals()`
- Audit trail with `created_by`, `updated_by` fields
- Caching methods with `cache()`, `fromCache()`, `clearCache()`
- Slug generation from the `name` field
- Standard timestamp and soft delete fields

## Best Practices

### Naming Conventions
- Use snake_case for field names
- Use PascalCase for DTO names
- Use descriptive names that match your database schema

### Validation
- Always add appropriate validation rules
- Use Laravel's built-in rules when possible
- Consider required=false fields carefully

### Organization
- Group related DTOs in subdirectories
- Use consistent namespacing
- Keep definitions focused and single-purpose

### Performance
- Only include fields you actually need
- Use relationships judiciously
- Consider the impact of nested DTOs on performance