# Laravel Arc

<div align="center">
  <img src="logo-header.svg" alt="Laravel Arc" width="400">
  <p><strong>Elegant and modern Data Transfer Objects (DTOs) management with automatic validation and direct property access</strong></p>
  
  ![PHP Version](https://img.shields.io/badge/php-%5E8.4-blue)
  ![Laravel Version](https://img.shields.io/badge/laravel-%5E12.19-red)
  ![License](https://img.shields.io/badge/license-MIT-green)
</div>

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [YAML Schema Documentation](#yaml-schema-documentation)
- [Field Types](#field-types)
- [Relationships](#relationships)
- [Validation Rules](#validation-rules)
- [CLI Commands](#cli-commands)
- [Examples](#examples)
- [Advanced Usage](#advanced-usage)
- [Contributing](#contributing)
- [License](#license)

## Features

- 🚀 **YAML-driven DTO generation** - Define your DTOs in simple YAML files
- 🔍 **Automatic validation** - Built-in Laravel validation rules support
- 🏗️ **Rich field types** - Support for 14+ field types including enums, UUIDs, and JSON
- 🔗 **Eloquent relationships** - Full support for Laravel relationship types
- ⚡ **Direct property access** - Clean, modern syntax with PHP 8.4+ features
- 🛠️ **CLI commands** - Powerful commands for DTO management and generation
- 📦 **Zero configuration** - Works out of the box with sensible defaults
- 🧪 **Fully tested** - Comprehensive test suite with 100% coverage

## Installation

Install Laravel Arc via Composer:

```bash
composer require grazulex/laravel-arc
```

The service provider will be automatically registered via Laravel's package discovery.

Publish the configuration file (optional):

```bash
php artisan vendor:publish --provider="Grazulex\LaravelArc\LaravelArcServiceProvider"
```

## Configuration

The package includes a configuration file `config/dto.php` with the following options:

```php
<?php

return [
    /*
     * DTO Definition Files Path
     * Path where your YAML definition files are located
     */
    'definitions_path' => base_path('database/dto_definitions'),

    /*
     * DTO Output Path  
     * Directory where generated DTO PHP classes will be written
     */
    'output_path' => base_path('app/DTOs'),
];
```

## Quick Start

1. **Create a DTO definition directory:**
   ```bash
   mkdir database/dto_definitions
   ```

2. **Create your first DTO definition (`database/dto_definitions/user.yaml`):**
   ```yaml
   header:
     dto: UserDTO
     table: users
     model: App\Models\User

   fields:
     id:
       type: uuid
       required: true
     name:
       type: string
       required: true
       rules: [min:2, max:100]
     email:
       type: string
       required: true
       rules: [email, unique:users]
     email_verified_at:
       type: datetime
       nullable: true

   relations:
     posts:
       type: hasMany
       target: App\Models\Post

   options:
     timestamps: true
     soft_deletes: false
     namespace: App\DTOs
   ```

3. **Generate the DTO programmatically:**
   ```php
   use Grazulex\LaravelArc\Generator\DtoGenerator;
   use Symfony\Component\Yaml\Yaml;
   
   $definition = Yaml::parseFile('database/dto_definitions/user.yaml');
   $generator = DtoGenerator::make();
   $code = $generator->generateFromDefinition($definition);
   
   // Save to your DTOs directory
   file_put_contents('app/DTOs/UserDTO.php', $code);
   ```

4. **Use your DTO:**
   ```php
   use App\DTOs\UserDTO;

   $user = new UserDTO();
   $user->name = 'John Doe';
   $user->email = 'john@example.com';
   ```

## YAML Schema Documentation

Laravel Arc uses YAML files to define DTO structures. Each YAML file must contain three main sections:

### Schema Structure

```yaml
header:
  # DTO configuration
  
fields:
  # Field definitions
  
relations:
  # Relationship definitions (optional)
  
options:
  # Generation options (optional)
```

### Header Section

The `header` section defines basic DTO information:

| Attribute | Type | Required | Description |
|-----------|------|----------|-------------|
| `dto` | string | Yes | Name of the generated DTO class |
| `table` | string | No | Database table name (for migration generation) |
| `model` | string | No | Associated Eloquent model class |

**Example:**
```yaml
header:
  dto: ProductDTO
  table: products
  model: App\Models\Product
```

### Fields Section

The `fields` section defines all DTO properties. Each field supports the following attributes:

#### Common Field Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `type` | string | - | Field type (see [Field Types](#field-types)) |
| `required` | boolean | false | Whether the field is required |
| `nullable` | boolean | false | Whether the field can be null |
| `default` | mixed | - | Default value for the field |
| `rules` | array | [] | Laravel validation rules |

**Example:**
```yaml
fields:
  name:
    type: string
    required: true
    rules: [min:2, max:100]
  price:
    type: float
    nullable: true
    default: 0.0
    rules: [min:0]
```

#### Type-Specific Attributes

**Enum Fields:**
```yaml
status:
  type: enum
  values: [draft, published, archived]
  default: draft
```

**Array Fields:**
```yaml
tags:
  type: array
  rules: [distinct]
  nullable: true
```

**DTO Fields (nested DTOs):**
```yaml
profile:
  type: dto
  dto: profile  # References another DTO definition
  nullable: true
```

### Relations Section

Define Eloquent relationships for your DTOs:

| Attribute | Type | Required | Description |
|-----------|------|----------|-------------|
| `type` | string | Yes | Relationship type |
| `target` | string | Yes | Target model class |

**Example:**
```yaml
relations:
  category:
    type: belongsTo
    target: App\Models\Category
  tags:
    type: belongsToMany
    target: App\Models\Tag
```

### Options Section

Configure DTO generation behavior:

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `timestamps` | boolean | false | Include created_at/updated_at fields |
| `soft_deletes` | boolean | false | Include deleted_at field |
| `expose_hidden_by_default` | boolean | false | Expose hidden model attributes |
| `namespace` | string | App\DTOs | DTO namespace |

**Example:**
```yaml
options:
  timestamps: true
  soft_deletes: false
  expose_hidden_by_default: false
  namespace: App\DTOs\Products
```

## Field Types

Laravel Arc supports the following field types:

### Primitive Types

| Type | PHP Type | Description | Example |
|------|----------|-------------|---------|
| `string` | string | Text field | `"Hello World"` |
| `integer` | int | Integer number | `42` |
| `float` | float | Floating point number | `3.14` |
| `boolean` | bool | True/false value | `true` |
| `array` | array | Array of values | `["a", "b", "c"]` |
| `json` | array | JSON data | `{"key": "value"}` |

### Specialized Types

| Type | PHP Type | Description | Example |
|------|----------|-------------|---------|
| `uuid` | string | UUID string | `"550e8400-e29b-41d4-a716-446655440000"` |
| `enum` | string | Enumeration value | `"published"` |
| `id` | int | Auto-incrementing ID | `1` |
| `text` | string | Long text field | `"Long content..."` |
| `decimal` | string | Decimal number | `"19.99"` |

### Date/Time Types

| Type | PHP Type | Description | Example |
|------|----------|-------------|---------|
| `datetime` | DateTime | Date and time | `"2024-01-15 14:30:00"` |
| `date` | DateTime | Date only | `"2024-01-15"` |
| `time` | DateTime | Time only | `"14:30:00"` |

### Custom Types

| Type | PHP Type | Description | Example |
|------|----------|-------------|---------|
| `dto` | object | Nested DTO | References another DTO definition |

## Relationships

Laravel Arc supports all Laravel Eloquent relationship types:

### Supported Relationship Types

| Type | Description | Example |
|------|-------------|---------|
| `belongsTo` | Many-to-one relationship | User belongs to Company |
| `hasOne` | One-to-one relationship | User has one Profile |
| `hasMany` | One-to-many relationship | User has many Posts |
| `belongsToMany` | Many-to-many relationship | User belongs to many Roles |

### Relationship Definition

```yaml
relations:
  # One-to-one
  profile:
    type: hasOne
    target: App\Models\Profile
    
  # One-to-many  
  posts:
    type: hasMany
    target: App\Models\Post
    
  # Many-to-one
  company:
    type: belongsTo
    target: App\Models\Company
    
  # Many-to-many
  roles:
    type: belongsToMany
    target: App\Models\Role
```

## Validation Rules

Laravel Arc supports all Laravel validation rules. Rules are defined as arrays in the field configuration:

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
    
  status:
    type: enum
    values: [active, inactive, pending]
    rules: [in:active,inactive,pending]
```

### Custom Validation Rules

You can use any Laravel validation rule, including custom rules:

```yaml
fields:
  custom_field:
    type: string
    rules: [required, string, 'App\Rules\CustomRule']
```

## CLI Commands

Laravel Arc provides CLI commands for DTO definition management:

### List Available DTOs

```bash
# List all DTO definition files
php artisan dto:definition-list

# Compact view (names only)
php artisan dto:definition-list --compact

# Custom path
php artisan dto:definition-list --path=/path/to/definitions
```

### Initialize DTO Definitions

```bash
# Create a new DTO definition file
php artisan dto:definition-init UserDTO --model=App\Models\User --table=users

# Create with custom path
php artisan dto:definition-init ProductDTO --model=App\Models\Product --table=products --path=/custom/path

# Force overwrite existing file
php artisan dto:definition-init UserDTO --model=App\Models\User --table=users --force
```

### Programmatic DTO Generation

DTOs are generated programmatically using the `DtoGenerator` class:

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

## Examples

### Basic Product DTO

```yaml
# database/dto_definitions/product.yaml
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
    nullable: true
  price:
    type: decimal
    rules: [numeric, min:0]
  is_active:
    type: boolean
    default: true
  tags:
    type: array
    nullable: true

relations:
  category:
    type: belongsTo
    target: App\Models\Category

options:
  timestamps: true
  soft_deletes: true
  namespace: App\DTOs
```

### User with Profile DTO

```yaml
# database/dto_definitions/user.yaml
header:
  dto: UserDTO
  table: users
  model: App\Models\User

fields:
  id:
    type: uuid
    required: true
  name:
    type: string
    required: true
    rules: [min:2, max:100]
  email:
    type: string
    required: true
    rules: [email, unique:users]
  email_verified_at:
    type: datetime
    nullable: true
  profile:
    type: dto
    dto: profile
    nullable: true

relations:
  posts:
    type: hasMany
    target: App\Models\Post
  roles:
    type: belongsToMany
    target: App\Models\Role

options:
  timestamps: true
  namespace: App\DTOs
```

```yaml
# database/dto_definitions/profile.yaml
header:
  dto: ProfileDTO
  table: profiles
  model: App\Models\Profile

fields:
  age:
    type: integer
    nullable: true
    rules: [min:13, max:120]
  bio:
    type: text
    nullable: true
    rules: [max:500]
  website:
    type: string
    nullable: true
    rules: [url]
  avatar:
    type: string
    nullable: true

options:
  timestamps: false
  namespace: App\DTOs
```

### Complex E-commerce DTO

```yaml
# database/dto_definitions/order.yaml
header:
  dto: OrderDTO
  table: orders
  model: App\Models\Order

fields:
  id:
    type: uuid
    required: true
  order_number:
    type: string
    required: true
    rules: [unique:orders]
  status:
    type: enum
    values: [pending, processing, shipped, delivered, cancelled]
    default: pending
  subtotal:
    type: decimal
    rules: [numeric, min:0]
  tax_amount:
    type: decimal
    rules: [numeric, min:0]
  total_amount:
    type: decimal
    rules: [numeric, min:0]
  currency:
    type: string
    default: USD
    rules: [size:3]
  shipping_address:
    type: json
    nullable: true
  metadata:
    type: json
    nullable: true

relations:
  customer:
    type: belongsTo
    target: App\Models\User
  items:
    type: hasMany
    target: App\Models\OrderItem

options:
  timestamps: true
  soft_deletes: true
  namespace: App\DTOs\Ecommerce
```

## Advanced Usage

### Custom Namespaces

Organize your DTOs with custom namespaces:

```yaml
options:
  namespace: App\DTOs\Ecommerce\Products
```

### Nested DTO Relationships

Create complex data structures with nested DTOs:

```yaml
# user.yaml
fields:
  profile:
    type: dto
    dto: profile
  billing_address:
    type: dto
    dto: address
  shipping_address:
    type: dto
    dto: address
```

### Environment-Specific Configuration

Use different configurations based on your environment by modifying your YAML definitions or using programmatic generation with conditional logic:

```php
// In your generation script
$config = [
    'timestamps' => true,
    'soft_deletes' => app()->environment('production'),
    'namespace' => app()->environment('testing') ? 'Tests\\DTOs' : 'App\\DTOs',
];
```

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Run tests: `composer test`
4. Check code quality: `composer pint && composer phpstan`

## License

Laravel Arc is open-sourced software licensed under the [MIT license](LICENSE).

## Support

- 🐛 [Report Issues](https://github.com/Grazulex/laravel-arc/issues)
- 💬 [Discussions](https://github.com/Grazulex/laravel-arc/discussions)
- 📖 [Wiki](https://github.com/Grazulex/laravel-arc/wiki)

---

<div align="center">
  <p>Made with ❤️ by <a href="https://github.com/Grazulex">Jean-Marc Strauven</a></p>
</div>