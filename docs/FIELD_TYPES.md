# Field Types Reference

Laravel Arc supports a comprehensive set of field types for your DTOs. This guide covers all available types with examples and usage patterns.

## Important Note

**Some examples in this guide use the deprecated `options` section.** The `options` section has been replaced with behavioral traits in the `header.traits` array. Please refer to the [YAML Schema Reference](YAML_SCHEMA.md) for the current syntax.

```yaml
# ✅ New format (recommended)
header:
  traits:
    - HasTimestamps

# ❌ Old format (deprecated)
options:
  timestamps: true
```

## Field Type Categories

### Primitive Types

#### String
```yaml
name:
  type: string
  required: true
  rules: [min:2, max:100]
```

#### Integer
```yaml
age:
  type: integer
  rules: [min:0, max:150]
```

#### Float
```yaml
price:
  type: float
  rules: [numeric, min:0]
```

#### Boolean
```yaml
is_active:
  type: boolean
  default: true
```

#### Array
```yaml
tags:
  type: array
  rules: [array, distinct]
```

#### JSON
```yaml
metadata:
  type: json
  rules: [json]
```

### Specialized Types

#### UUID
```yaml
id:
  type: uuid
  required: true
```

#### ID (Auto-incrementing)
```yaml
id:
  type: id
  required: true
```

#### Text (Long text)
```yaml
description:
  type: text
  rules: [max:1000]
```

#### Decimal
```yaml
price:
  type: decimal
  rules: [numeric, min:0]
```

### Date/Time Types

#### DateTime
```yaml
created_at:
  type: datetime
  rules: [date]
```

#### Date
```yaml
birth_date:
  type: date
  rules: [date, before:today]
```

#### Time
```yaml
start_time:
  type: time
  rules: [date_format:H:i:s]
```

### Enum Types

Laravel Arc supports both traditional enums and modern PHP enum classes.

#### Traditional Enum with Values
```yaml
status:
  type: enum
  values: [draft, published, archived]
  default: draft
  required: true
```

#### PHP Enum Class (Recommended)
```yaml
status:
  type: enum
  class: App\Enums\Status
  default: draft
  required: true
```

**Example PHP Enum:**
```php
<?php

namespace App\Enums;

enum Status: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
```

#### Int Enum
```yaml
priority:
  type: enum
  class: App\Enums\Priority
  default: 2
  required: false
```

```php
<?php

namespace App\Enums;

enum Priority: int
{
    case LOW = 1;
    case MEDIUM = 2;
    case HIGH = 3;
}
```

### Nested DTO Types

#### Simple Nested DTO
```yaml
profile:
  type: dto
  dto: ProfileDTO
  required: true
```

#### Optional Nested DTO
```yaml
billing_address:
  type: dto
  dto: AddressDTO
  required: false
```

## Field Attributes

### Common Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `type` | string | - | Field type (required) |
| `required` | boolean | false | Whether the field is required |
| `default` | mixed | - | Default value for the field |
| `rules` | array | [] | Laravel validation rules |

### Type-Specific Attributes

#### Enum Fields

**Traditional Enum:**
```yaml
status:
  type: enum
  values: [active, inactive, pending]
  default: active
  rules: [in:active,inactive,pending]
```

**PHP Enum Class:**
```yaml
status:
  type: enum
  class: App\Enums\Status
  default: active
  # Laravel automatically adds enum:App\Enums\Status rule
```

#### Array Fields
```yaml
tags:
  type: array
  rules: [array, distinct, min:1]
```

#### DTO Fields
```yaml
profile:
  type: dto
  dto: ProfileDTO
  required: true
```

## Validation Rules

### Common Validation Examples

```yaml
fields:
  email:
    type: string
    rules: [email, unique:users, max:255]
    
  age:
    type: integer
    rules: [min:18, max:120]
    
  price:
    type: float
    rules: [numeric, min:0]
    
  tags:
    type: array
    rules: [array, distinct]
    
  website:
    type: string
    rules: [url, nullable]
```

### Custom Validation Rules

```yaml
fields:
  custom_field:
    type: string
    rules: [required, string, 'App\Rules\CustomRule']
```

## Best Practices

### 1. Use Appropriate Types
```yaml
# Good
price:
  type: decimal
  rules: [numeric, min:0]

# Avoid
price:
  type: string
  rules: [regex:/^\d+\.\d{2}$/]
```

### 2. Leverage PHP Enums
```yaml
# Recommended
status:
  type: enum
  class: App\Enums\Status
  default: draft

# Less preferred
status:
  type: enum
  values: [draft, published, archived]
  default: draft
```

### 3. Use Nested DTOs for Complex Data
```yaml
# Good structure
user:
  type: dto
  dto: UserDTO
  required: true

# Avoid flat structures for complex data
user_name:
  type: string
user_email:
  type: string
user_age:
  type: integer
```

### 4. Set Reasonable Defaults
```yaml
is_active:
  type: boolean
  default: true
  
priority:
  type: enum
  class: App\Enums\Priority
  default: App\Enums\Priority::MEDIUM
```

## Advanced Examples

### E-commerce Product DTO
```yaml
header:
  dto: ProductDTO
  table: products
  model: App\Models\Product

fields:
  id:
    type: uuid
    required: true
  name:
    type: string
    required: true
    rules: [min:2, max:255]
  description:
    type: text
    rules: [max:1000]
  price:
    type: decimal
    rules: [numeric, min:0]
  currency:
    type: enum
    class: App\Enums\Currency
    default: App\Enums\Currency::USD
  status:
    type: enum
    class: App\Enums\ProductStatus
    default: draft
  tags:
    type: array
    rules: [array, distinct]
  metadata:
    type: json
  is_featured:
    type: boolean
    default: false
  category:
    type: dto
    dto: CategoryDTO
    required: true

options:
  timestamps: true
  namespace: App\DTO\Ecommerce
```

### User Profile DTO
```yaml
header:
  dto: UserProfileDTO
  table: user_profiles
  model: App\Models\UserProfile

fields:
  id:
    type: uuid
    required: true
  user_id:
    type: uuid
    required: true
  first_name:
    type: string
    required: true
    rules: [min:2, max:50]
  last_name:
    type: string
    required: true
    rules: [min:2, max:50]
  birth_date:
    type: date
    rules: [date, before:today]
  gender:
    type: enum
    class: App\Enums\Gender
    required: false
  phone:
    type: string
    rules: [phone, nullable]
  address:
    type: dto
    dto: AddressDTO
    required: false
  preferences:
    type: json
  avatar_url:
    type: string
    rules: [url, nullable]

options:
  timestamps: true
  namespace: App\DTO\User
```

## Migration from Other Field Types

### From Laravel Schema Builder
```php
// Laravel Schema
$table->string('name');
$table->text('description');
$table->decimal('price', 8, 2);
$table->boolean('is_active');
$table->json('metadata');
$table->timestamps();
```

```yaml
# Laravel Arc DTO
fields:
  name:
    type: string
  description:
    type: text
  price:
    type: decimal
  is_active:
    type: boolean
  metadata:
    type: json

options:
  timestamps: true
```

### From Eloquent Model
```php
// Eloquent Model
protected $fillable = [
    'name', 'email', 'password', 'is_admin'
];

protected $casts = [
    'is_admin' => 'boolean',
    'email_verified_at' => 'datetime',
];
```

```yaml
# Laravel Arc DTO
fields:
  name:
    type: string
    required: true
  email:
    type: string
    required: true
    rules: [email]
  password:
    type: string
    required: true
  is_admin:
    type: boolean
    default: false
  email_verified_at:
    type: datetime
```

## See Also

- [YAML Schema Documentation](YAML_SCHEMA.md)
- [Relationships Guide](RELATIONSHIPS.md)
- [Validation Rules](VALIDATION_RULES.md)
- [Examples](../examples/README.md)