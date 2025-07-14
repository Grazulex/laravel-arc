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
| `namespace` | string | Namespace for the generated DTO | `App\DTO` |
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
  namespace: App\DTO
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

## Field Transformers

Field transformers allow you to automatically transform field values during DTO creation. Transformers are applied before validation and can be chained together.

### Available Transformers

| Transformer | Description | Example |
|-------------|-------------|---------|
| `trim` | Removes whitespace from strings | `"  hello  "` → `"hello"` |
| `lowercase` | Converts strings to lowercase | `"Hello"` → `"hello"` |
| `uppercase` | Converts strings to uppercase | `"hello"` → `"HELLO"` |
| `title_case` | Converts strings to title case | `"hello world"` → `"Hello World"` |
| `slugify` | Converts strings to URL-friendly slugs | `"Hello World!"` → `"hello-world"` |
| `abs` | Returns absolute value of numbers | `-5` → `5` |
| `encrypt` | Encrypts string values | `"secret"` → `"encrypted_value"` |
| `normalize_phone` | Normalizes phone numbers | `"01 23 45 67 89"` → `"+33123456789"` |
| `clamp_max:value` | Limits maximum value | `100` with `clamp_max:50` → `50` |
| `clamp_min:value` | Limits minimum value | `10` with `clamp_min:20` → `20` |

### Transformer Syntax

Transformers are applied in the order they're specified:

```yaml
fields:
  name:
    type: string
    transformers: [trim, title_case]
  
  email:
    type: string
    transformers: [trim, lowercase]
  
  phone:
    type: string
    transformers: [normalize_phone]
  
  price:
    type: float
    transformers: [abs, clamp_min:0, clamp_max:9999.99]
```

### Custom Transformers

You can register custom transformers in your application:

```php
use Grazulex\LaravelArc\Support\Transformers\FieldTransformerRegistry;

$registry = app(FieldTransformerRegistry::class);
$registry->register('capitalize_first', function ($value) {
    return is_string($value) ? ucfirst(strtolower($value)) : $value;
});
```

## Complete Example

Here's a comprehensive example showing all features including behavioral traits and transformers:

```yaml
# database/dto_definitions/product.yaml
header:
  dto: ProductDTO
  table: products
  model: App\Models\Product
  namespace: App\DTO\Catalog
  traits:
    - HasTimestamps
    - HasSoftDeletes
    - HasUuid
    - HasVersioning
    - HasTagging
    - HasAuditing
    - HasCaching
  use:
    - App\Traits\CustomTrait
  extends: BaseDTO

fields:
  # Basic fields with transformers
  name:
    type: string
    required: true
    rules: [min:2, max:255]
    transformers: [trim, title_case]
  
  description:
    type: text
    rules: [max:1000]
    transformers: [trim]
  
  # Numeric fields
  price:
    type: decimal
    rules: [numeric, min:0]
    transformers: [abs]
  
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
  namespace: App\DTO
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
    validation: [required, string, max:255]
  email:
    type: string
    validation: [required, email]
  status:
    type: string
    default: "active"
    validation: [required, in:active,inactive]
```

This example will generate a DTO with:
- UUID primary key with helper methods from HasUuid trait
- Version tracking with `nextVersion()` and `isNewerThan()` from HasVersioning trait
- Tag management with `addTag()`, `removeTag()`, `hasTag()` from HasTagging trait
- Audit trail with `created_by`, `updated_by` fields from HasAuditing trait
- Caching methods with `cache()`, `fromCache()`, `clearCache()` from HasCaching trait
- Standard timestamp and soft delete fields from HasTimestamps and HasSoftDeletes traits
- Field transformers for automatic data cleaning and formatting

## Best Practices

### Naming Conventions
- Use snake_case for field names
- Use PascalCase for DTO names
- Use descriptive names that match your database schema

### Validation
- Always add appropriate validation rules
- Use Laravel's built-in rules when possible
- Consider required=false fields carefully

### Transformers
- Apply transformers in logical order (e.g., `trim` before `title_case`)
- Use transformers to ensure data consistency
- Consider performance impact of complex transformers

### Behavioral Traits
- Only include traits you actually need
- Use HasTimestamps for DTOs representing models with timestamps
- Use HasUuid for DTOs with UUID primary keys
- Use HasSoftDeletes for DTOs representing soft-deletable models

### Organization
- Group related DTOs in subdirectories
- Use consistent namespacing
- Keep definitions focused and single-purpose

### Performance
- Only include fields you actually need
- Use relationships judiciously
- Consider the impact of nested DTOs on performance