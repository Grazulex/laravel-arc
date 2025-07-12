# Getting Started with Laravel Arc

This guide will help you quickly get started with Laravel Arc, a powerful Laravel package for managing Data Transfer Objects (DTOs) with automatic validation and direct property access.

## Installation

Install Laravel Arc via Composer:

```bash
composer require grazulex/laravel-arc
```

The service provider will be automatically registered via Laravel's package discovery.

## Configuration

Publish the configuration file (optional):

```bash
php artisan vendor:publish --provider="Grazulex\LaravelArc\LaravelArcServiceProvider"
```

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

### 1. Create a DTO definition directory

```bash
mkdir database/dto_definitions
```

### 2. Create your first DTO definition

Create `database/dto_definitions/user.yaml`:

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

relations:
  posts:
    type: hasMany
    target: App\Models\Post

options:
  timestamps: true
  namespace: App\DTOs
```

### 3. Generate the DTO

```bash
php artisan dto:generate user.yaml
```

### 4. Use your DTO

```php
use App\DTOs\UserDTO;

$user = new UserDTO();
$user->name = 'John Doe';
$user->email = 'john@example.com';
```

## Next Steps

- 📘 [YAML Schema Documentation](YAML_SCHEMA.md) - Learn about YAML structure and options
- 🔧 [Field Types Reference](FIELD_TYPES.md) - Explore all available field types
- 🔗 [Relationships Guide](RELATIONSHIPS.md) - Working with Eloquent relationships
- 🖥️ [CLI Commands](CLI_COMMANDS.md) - Master the command-line tools
- 📚 [Examples](../examples/README.md) - See real-world examples
- 🚀 [Advanced Usage](ADVANCED_USAGE.md) - Advanced features and techniques

## Basic Examples

### Simple Product DTO

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
  price:
    type: decimal
    rules: [numeric, min:0]
  is_active:
    type: boolean
    default: true

options:
  timestamps: true
  namespace: App\DTOs
```

### Generate and Use

```bash
php artisan dto:generate product.yaml
```

```php
use App\DTOs\ProductDTO;

$product = new ProductDTO();
$product->name = 'Laptop';
$product->price = 999.99;
$product->is_active = true;
```

## Troubleshooting

### Common Issues

**YAML Syntax Errors**: Ensure proper indentation and syntax
```bash
# Check your YAML file
php artisan dto:generate your-file.yaml --dry-run
```

**File Not Found**: Verify the definitions path
```bash
# List available definitions
php artisan dto:definition-list
```

**Generation Errors**: Check the detailed error messages
```bash
# Force regeneration
php artisan dto:generate your-file.yaml --force
```

## Support

- 🐛 [Report Issues](https://github.com/Grazulex/laravel-arc/issues)
- 💬 [Discussions](https://github.com/Grazulex/laravel-arc/discussions)
- 📖 [Wiki](https://github.com/Grazulex/laravel-arc/wiki)