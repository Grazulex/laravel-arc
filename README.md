# Laravel Arc

<div align="center">
  <img src="logo-header.svg" alt="Laravel Arc" width="400">
  <p><strong>Elegant and modern Data Transfer Objects (DTOs) for Laravel with automatic validation and collection management</strong></p>
  
  [![Latest Version](https://img.shields.io/packagist/v/grazulex/laravel-arc)](https://packagist.org/packages/grazulex/laravel-arc)
  [![Total Downloads](https://img.shields.io/packagist/dt/grazulex/laravel-arc)](https://packagist.org/packages/grazulex/laravel-arc)
  [![License](https://img.shields.io/github/license/grazulex/laravel-arc)](LICENSE.md)
  [![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue)](https://php.net)
  [![Laravel Version](https://img.shields.io/badge/laravel-%5E12.19-red)](https://laravel.com)
  [![Tests](https://github.com/Grazulex/laravel-arc/workflows/Tests/badge.svg)](https://github.com/Grazulex/laravel-arc/actions)
  [![Code Style](https://img.shields.io/badge/code%20style-pint-orange)](https://github.com/laravel/pint)
</div>

## Overview

Laravel Arc is a powerful Laravel package that simplifies Data Transfer Object (DTO) management through YAML-driven generation. Define your DTOs in simple YAML files and let Laravel Arc generate type-safe, validated PHP classes with automatic property access and comprehensive collection support.

**Think of it as Laravel API Resources, but with stronger typing, automatic validation, and generated from YAML definitions.**

## ✨ Key Features

- 🚀 **YAML-driven generation** - Define DTOs in simple, readable YAML files
- 🔍 **Automatic validation** - Built-in Laravel validation rules support
- 🏗️ **Rich field types** - 14+ field types including enums, UUIDs, nested DTOs, and JSON
- 🔗 **Eloquent relationships** - Full support for Laravel relationship types
- ⚡ **Direct property access** - Clean, modern syntax with PHP 8.3+ features
- 📦 **Collection management** - Convert models to DTO collections like Laravel Resources
- 🎯 **Powerful trait system** - Built-in traits for validation, data conversion, and utilities
- 📤 **Multiple export formats** - Export DTOs in 9 formats (JSON, YAML, CSV, XML, TOML, Markdown, PHP Array, Query String, MessagePack)
- 🛠️ **Powerful CLI commands** - Generate, list, and manage DTOs from the command line
- 📁 **Smart path resolution** - Automatic namespace-to-path conversion with custom organization
- 🚨 **Enhanced error handling** - Detailed error messages with actionable suggestions
- 📦 **Zero configuration** - Works out of the box with sensible defaults
- 🧪 **Fully tested** - Comprehensive test suite with high coverage

## 🚀 Quick Start

### Installation

```bash
composer require grazulex/laravel-arc
```

### Basic Usage

**1. Create a DTO definition**
```bash
php artisan dto:definition-init UserDTO --model=App\\Models\\User
```

**2. Define your DTO in YAML**
```yaml
# dto-definitions/UserDTO.yaml
name: UserDTO
namespace: App\DTOs

fields:
  id:
    type: integer
    validation: [required, integer]
  
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

**3. Generate your DTO**
```bash
php artisan dto:generate UserDTO.yaml
```

**4. Use your DTO**
```php
// Convert a model to DTO
$user = User::find(1);
$userDto = UserDTO::fromModel($user);

// Convert a collection to DTO collection (like Laravel Resources)
$users = User::all();
$userDtos = UserDTO::collection($users); // Returns DtoCollection
// OR
$userDtos = UserDTO::fromModels($users); // Alternative syntax

// API Resource format
return response()->json($userDtos->toArrayResource());
// Output: {"data": [{"id": 1, "name": "John", "email": "john@example.com", "status": "active"}]}

// Export in multiple formats
$yaml = $userDto->toYaml();
$csv = $userDto->toCsv();
$xml = $userDto->toXml();
$markdown = $userDto->toMarkdownTable();

// Collection exports
$csvData = UserDTO::collectionToCsv($users);
$xmlData = UserDTO::collectionToXml($users);
$yamlData = UserDTO::collectionToYaml($users);

// Validation
$userDto = UserDTO::fromArray($request->all());
if (!$userDto->isValid()) {
    return response()->json(['errors' => $userDto->getErrors()], 422);
}
```

## 📖 Documentation

**Complete documentation and guides:**
- **[📖 Documentation Index](docs/README.md)** - Complete navigation guide
- **[🚀 Getting Started](docs/GETTING_STARTED.md)** - Installation and first DTO
- **[📘 DTO Usage Guide](docs/DTO_USAGE_GUIDE.md)** - How to use DTOs in your Laravel application
- **[🎯 Examples Collection](examples/README.md)** - Working examples and templates

### Core Concepts
- **[YAML Schema](docs/YAML_SCHEMA.md)** - Full YAML configuration reference
- **[Field Types](docs/FIELD_TYPES.md)** - All available field types and options
- **[Validation Rules](docs/VALIDATION_RULES.md)** - Custom validation and error handling

### Advanced Features
- **[Collection Management](docs/COLLECTION_MANAGEMENT.md)** - Working with DTO collections and API resources
- **[Export Formats](docs/EXPORT_FORMATS.md)** - Export DTOs in 9 different formats
- **[Relationships](docs/RELATIONSHIPS.md)** - Eloquent relationships in DTOs
- **[Nested DTOs](docs/NESTED_DTO_GUIDE.md)** - Building complex nested structures
- **[CLI Commands](docs/CLI_COMMANDS.md)** - All available Artisan commands
- **[Advanced Usage](docs/ADVANCED_USAGE.md)** - Advanced patterns and customizations

## 🎯 Use Cases

Laravel Arc is perfect for:

- **API Development** - Type-safe request/response handling
- **Data Validation** - Consistent validation across your application
- **Model Transformation** - Clean data layer separation
- **Complex Forms** - Nested form validation and processing
- **API Resources** - Alternative to Laravel Resources with stronger typing

## � Configuration

Laravel Arc works out of the box, but you can customize it:

```php
// config/dto.php
return [
    'namespace' => 'App\\DTOs',
    'output_path' => app_path('DTOs'),
    'definitions_path' => base_path('dto-definitions'),
];
```

## 📚 Examples

Check out the [examples directory](examples/) for complete working examples:
- **[Basic User DTO](examples/user.yaml)** - Simple user DTO with validation
- **[API Controllers](examples/api-controller-example.php)** - Using DTOs in API controllers
- **[Export Formats](examples/export-formats-example.php)** - Export DTOs in 9 different formats
- **[Collection Methods](examples/collection-methods-example.php)** - Advanced collection management
- **[Nested Structures](examples/nested-order.yaml)** - Complex nested DTOs
- **[Enum Support](examples/enum-examples.yaml)** - Working with PHP enums

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## 📄 License

Laravel Arc is open-sourced software licensed under the [MIT license](LICENSE.md).

---

<div align="center">
  Made with ❤️ for the Laravel community
</div>