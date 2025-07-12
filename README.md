# Laravel Arc

<div align="center">
  <img src="logo-header.svg" alt="Laravel Arc" width="400">
  <p><strong>Elegant and modern Data Transfer Objects (DTOs) management with automatic validation and direct property access</strong></p>
  
  [![Latest Version](https://img.shields.io/packagist/v/grazulex/laravel-arc)](https://packagist.org/packages/grazulex/laravel-arc)
  [![Total Downloads](https://img.shields.io/packagist/dt/grazulex/laravel-arc)](https://packagist.org/packages/grazulex/laravel-arc)
  [![License](https://img.shields.io/github/license/grazulex/laravel-arc)](LICENSE.md)
  [![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue)](https://php.net)
  [![Laravel Version](https://img.shields.io/badge/laravel-%5E12.19-red)](https://laravel.com)
  [![Tests](https://github.com/Grazulex/laravel-arc/workflows/Tests/badge.svg)](https://github.com/Grazulex/laravel-arc/actions)
  [![Code Style](https://img.shields.io/badge/code%20style-pint-orange)](https://github.com/laravel/pint)
</div>

## Overview

Laravel Arc is a powerful Laravel package that simplifies Data Transfer Object (DTO) management through YAML-driven generation. Define your DTOs in simple YAML files and let Laravel Arc generate type-safe, validated PHP classes with automatic property access and comprehensive relationship support.

## ✨ Key Features

- 🚀 **YAML-driven generation** - Define DTOs in simple, readable YAML files
- 🔍 **Automatic validation** - Built-in Laravel validation rules support
- 🏗️ **Rich field types** - 14+ field types including enums, UUIDs, nested DTOs, and JSON
- 🔗 **Eloquent relationships** - Full support for Laravel relationship types
- ⚡ **Direct property access** - Clean, modern syntax with PHP 8.3+ features
- 🛠️ **Powerful CLI commands** - Generate, list, and manage DTOs from the command line
- 📁 **Smart path resolution** - Automatic namespace-to-path conversion with custom organization
- 🚨 **Enhanced error handling** - Detailed error messages with actionable suggestions
- 📦 **Zero configuration** - Works out of the box with sensible defaults
- 🧪 **Fully tested** - Comprehensive test suite with high coverage

## 🚀 Quick Start

Get started with Laravel Arc in minutes:


### 1. Install the package
```bash
composer require grazulex/laravel-arc
```

### 2a. Create a new DTO definition via a command
```bash
php artisan dto:definition-init UserDTO --model=App\Models\User
```

OR

### 2b.1 Create manually your first DTO definition
```bash
mkdir database/dto_definitions
```

### 2b.2 Create database/dto_definitions/user.yaml
```yaml
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
    rules: [min:2, max:100]
  email:
    type: string
    required: true
    rules: [email, unique:users]

options:
  timestamps: true
  namespace: App\DTOs
```

### 3. Generate the DTO

```bash
php artisan dto:generate user.yaml
```

### 4. Use in your code
```php
$user = new UserDTO();
$user->name = 'John Doe';
$user->email = 'john@example.com';
```

## 📚 Documentation

### Getting Started
- 📖 [Installation & Setup](docs/GETTING_STARTED.md) - Complete setup guide with examples
- 🔧 [Configuration](docs/GETTING_STARTED.md#configuration) - Customize paths and settings

### Core Concepts
- 📝 [YAML Schema Reference](docs/YAML_SCHEMA.md) - Complete YAML structure documentation
- 🏷️ [Field Types Guide](docs/FIELD_TYPES.md) - All available field types with examples
- 🔗 [Relationships](docs/RELATIONSHIPS.md) - Working with Eloquent relationships
- ✅ [Validation Rules](docs/FIELD_TYPES.md#validation-rules) - Laravel validation integration

### Tools & Commands
- 🖥️ [CLI Commands](docs/CLI_COMMANDS.md) - Master the command-line interface
- 📊 [Examples Collection](examples/README.md) - Real-world examples and patterns

### Advanced Features
- 🚀 [Advanced Usage](docs/ADVANCED_USAGE.md) - Programmatic generation and custom patterns
- 🔄 [Nested DTOs](docs/NESTED_DTO_GUIDE.md) - Complex nested relationships
- 📁 [Path Resolution](docs/DTO_PATH_RESOLVER_GUIDE.md) - Custom namespace organization
- 🚨 [Error Handling](docs/DTO_GENERATION_EXCEPTION_GUIDE.md) - Comprehensive error management
- 🔢 [Enum Support](docs/ENUM_CUSTOM_RULES.md) - Advanced enum validation

## 🎯 Use Cases

Laravel Arc is perfect for:

- **API Development** - Generate consistent DTOs for API responses
- **Form Validation** - Create validated data structures for form handling
- **Data Migration** - Transform data between different formats
- **Clean Architecture** - Separate data concerns from business logic
- **Microservices** - Standardize data transfer between services

## 🛠️ Common Commands

```bash
# Generate a single DTO
php artisan dto:generate user.yaml

# Generate all DTOs
php artisan dto:generate --all

# List available definitions
php artisan dto:definition-list

# Create a new definition
php artisan dto:definition-init ProductDTO --model=App\Models\Product

# Preview generated code
php artisan dto:generate user.yaml --dry-run
```

## 📦 Package Information

- **Package**: `grazulex/laravel-arc`
- **Packagist**: https://packagist.org/packages/grazulex/laravel-arc
- **License**: MIT
- **PHP Requirements**: ^8.3
- **Laravel Requirements**: ^12.19

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

```bash
# Clone the repository
git clone https://github.com/Grazulex/laravel-arc.git
cd laravel-arc

# Install dependencies
composer install

# Run tests
composer test

# Check code quality
composer pint && composer phpstan
```

## 🆘 Support & Community

- 🐛 [Report Issues](https://github.com/Grazulex/laravel-arc/issues)
- 💬 [Discussions](https://github.com/Grazulex/laravel-arc/discussions)
- 📖 [Wiki](https://github.com/Grazulex/laravel-arc/wiki)
- 📧 [Email Support](mailto:jms@grazulex.be)

## 📄 License

Laravel Arc is open-sourced software licensed under the [MIT license](LICENSE.md).

---

<div align="center">
  <p>Made with ❤️ by <a href="https://github.com/Grazulex">Jean-Marc Strauven</a></p>
  <p>
    <a href="https://packagist.org/packages/grazulex/laravel-arc">
      <img src="https://img.shields.io/packagist/v/grazulex/laravel-arc" alt="Latest Version">
    </a>
    <a href="https://github.com/Grazulex/laravel-arc/stargazers">
      <img src="https://img.shields.io/github/stars/Grazulex/laravel-arc" alt="Stars">
    </a>
    <a href="https://github.com/Grazulex/laravel-arc/network/members">
      <img src="https://img.shields.io/github/forks/Grazulex/laravel-arc" alt="Forks">
    </a>
  </p>
</div>