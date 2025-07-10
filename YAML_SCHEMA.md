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
  
options:
  # Generation options (optional)
```

## Header Section

The header section contains metadata about the DTO being generated.

### Required Attributes

| Attribute | Type | Description | Example |
|-----------|------|-------------|---------|
| `dto` | string | Name of the DTO class to generate | `UserDTO` |

### Optional Attributes

| Attribute | Type | Description | Example |
|-----------|------|-------------|---------|
| `table` | string | Database table name (for reference) | `users` |
| `model` | string | Associated Eloquent model class | `App\Models\User` |

### Example

```yaml
header:
  dto: UserDTO
  table: users
  model: App\Models\User
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
```yaml
status:
  type: enum
  values: [draft, published, archived]
  default: draft
```

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
    rules: [nullable, integer, min:13, max:120]
  
  website:
    type: string
    rules: [nullable, url, max:255]
  
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
    nullable: true
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

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `timestamps` | boolean | `false` | Include `created_at` and `updated_at` fields |
| `soft_deletes` | boolean | `false` | Include `deleted_at` field |
| `expose_hidden_by_default` | boolean | `false` | Expose hidden model attributes |
| `namespace` | string | `App\DTOs` | PHP namespace for the generated DTO |

### Example

```yaml
options:
  timestamps: true
  soft_deletes: false
  expose_hidden_by_default: false
  namespace: App\DTOs\Products
```

## Complete Example

Here's a comprehensive example showing all features:

```yaml
# database/dto_definitions/product.yaml
header:
  dto: ProductDTO
  table: products
  model: App\Models\Product

fields:
  # Basic fields
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
  
  # Enum field
  status:
    type: enum
    values: [draft, published, archived]
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
  timestamps: true
  soft_deletes: true
  expose_hidden_by_default: false
  namespace: App\DTOs\Catalog
```

## Best Practices

### Naming Conventions
- Use snake_case for field names
- Use PascalCase for DTO names
- Use descriptive names that match your database schema

### Validation
- Always add appropriate validation rules
- Use Laravel's built-in rules when possible
- Consider nullable fields carefully

### Organization
- Group related DTOs in subdirectories
- Use consistent namespacing
- Keep definitions focused and single-purpose

### Performance
- Only include fields you actually need
- Use relationships judiciously
- Consider the impact of nested DTOs on performance