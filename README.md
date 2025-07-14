# Laravel Arc

<div align="center">
  <img src="logo.png" alt="Laravel Arc" width="100">
  <p><strong>Generate modern, type-safe Data Transfer Objects (DTOs) in Laravel from clean YAML definitions — with automatic validation, nested support, and fluent collection handling.</strong></p>
  
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

## 🏗️ Architecture

```mermaid
graph TD
    A[YAML Definition] --> B[DTO Generator]
    B --> C[Generated DTO Class]
    
    A --> D[Field Types]
    A --> E[Transformers]
    A --> F[Validation Rules]
    A --> G[Behavioral Traits]
    
    C --> H[Functional Traits]
    H --> I[ValidatesData]
    H --> J[ConvertsData]
    H --> K[DtoUtilities]
    
    G --> L[HasTimestamps]
    G --> M[HasUuid]
    G --> N[HasSoftDeletes]
    G --> O[HasVersioning]
    G --> P[HasTagging]
    G --> Q[HasAuditing]
    G --> R[HasCaching]
    
    E --> S[String Transformers]
    E --> T[Numeric Transformers]
    E --> U[Custom Transformers]
    
    C --> V[Model Integration]
    C --> W[Collection Support]
    C --> X[Export Formats]
    
    style A fill:#e1f5fe
    style C fill:#c8e6c9
    style H fill:#fff3e0
    style G fill:#f3e5f5
    style E fill:#fce4ec
```

## ✨ Key Features

- 🚀 **YAML-driven generation** - Define DTOs in simple, readable YAML files
- 🔍 **Automatic validation** - Built-in Laravel validation rules support
- 🏗️ **Rich field types** - 14+ field types including enums, UUIDs, nested DTOs, and JSON
- 🔗 **Eloquent relationships** - Full support for Laravel relationship types
- ⚡ **Direct property access** - Clean, modern syntax with PHP 8.3+ features
- 📦 **Collection management** - Convert models to DTO collections like Laravel Resources
- 🎯 **Powerful trait system** - Built-in behavioral traits for common functionality (HasTimestamps, HasUuid, HasSoftDeletes, HasVersioning, HasTagging, HasAuditing, HasCaching) plus 3 functional traits (ValidatesData, ConvertsData, DtoUtilities) in every DTO
- 🔄 **Field transformers** - Automatically transform field values during DTO creation with built-in transformers (trim, lowercase, uppercase, title_case, slugify, abs, encrypt, normalize_phone, clamp_max, clamp_min)
- 📤 **Multiple export formats** - Export DTOs in 10 formats (JSON, YAML, CSV, XML, TOML, Markdown, HTML, PHP Array, Query String, MessagePack) with dedicated collection methods
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
php artisan dto:definition-init UserDTO --model=App\\Models\\User --table=users
```

**2. Define your DTO in YAML**
```yaml
# dto-definitions/UserDTO.yaml
header:
  dto: UserDTO
  namespace: App\DTOs
  model: App\Models\User
  traits:
    - HasTimestamps
    - HasUuid

fields:
  name:
    type: string
    validation: [required, string, max:255]
    transformers: [trim, title_case]
  
  email:
    type: string
    validation: [required, email]
    transformers: [trim, lowercase]
  
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

// Export in multiple formats - 10 formats available
$json = $userDto->toJson();
$yaml = $userDto->toYaml();
$csv = $userDto->toCsv();
$xml = $userDto->toXml();
$toml = $userDto->toToml();
$markdown = $userDto->toMarkdownTable();
$html = $userDto->toHtml();
$phpArray = $userDto->toPhpArray();
$queryString = $userDto->toQueryString();
$messagepack = $userDto->toMessagePack();
$collection = $userDto->toCollection();

// Collection exports (with data wrapper like Laravel Resources)
$jsonData = UserDTO::collectionToJson($users);
$csvData = UserDTO::collectionToCsv($users);
$xmlData = UserDTO::collectionToXml($users);
$yamlData = UserDTO::collectionToYaml($users);
$htmlData = UserDTO::collectionToHtml($users);
$markdownData = UserDTO::collectionToMarkdownTable($users);

// Validation
$userDto = UserDTO::fromArray($request->all());
if (!$userDto->isValid()) {
    return response()->json(['errors' => $userDto->getErrors()], 422);
}
```

## 🎯 Traits System

Laravel Arc provides a powerful trait system with two types of traits:

### Functional Traits (Automatic)
Every DTO automatically includes these three powerful traits:
- **ValidatesData** - Provides validation methods (`validate()`, `passes()`, `fails()`)
- **ConvertsData** - Provides conversion methods (`toJson()`, `toCsv()`, `toXml()`, etc.)
- **DtoUtilities** - Provides utility methods (`getProperties()`, `with()`, `equals()`)

### Behavioral Traits (Optional)
Add specific functionality by including traits in your YAML definition:

```yaml
header:
  traits:
    - HasTimestamps    # Adds created_at, updated_at fields and methods
    - HasUuid         # Adds id field with UUID validation
    - HasSoftDeletes  # Adds deleted_at field for soft deletes
    - HasVersioning   # Adds version field and versioning methods
    - HasTagging      # Adds tags field and tagging methods
    - HasAuditing     # Adds audit trail fields and methods
    - HasCaching      # Adds caching capabilities
```

**Example usage:**
```php
// Using functional traits (automatic)
$userDto = UserDTO::fromArray($data);
if (UserDTO::passes($data)) {
    $validated = UserDTO::validate($data);
}

// Using behavioral traits (if included)
$userDto = $userDto->addTag('premium')
                  ->nextVersion()
                  ->touch()
                  ->cache(3600);
```

## 🔄 Field Transformers

Automatically transform field values during DTO creation:

```yaml
fields:
  name:
    type: string
    transformers: [trim, title_case]  # "  john doe  " → "John Doe"
  
  email:
    type: string
    transformers: [trim, lowercase]   # "  JOHN@EXAMPLE.COM  " → "john@example.com"
  
  price:
    type: decimal
    transformers: [abs, clamp_min:0]  # -19.99 → 19.99
```

**Available transformers:**
- **String transformers**: `trim`, `lowercase`, `uppercase`, `title_case`, `slugify`
- **Numeric transformers**: `abs`, `clamp_max`, `clamp_min`
- **Security transformers**: `encrypt`
- **Phone transformers**: `normalize_phone` (adds +33 prefix for French numbers starting with 0)

## 📖 Documentation

**Complete documentation and guides:**
- **[📖 Documentation Index](docs/README.md)** - Complete navigation guide
- **[🚀 Getting Started](docs/GETTING_STARTED.md)** - Installation and first DTO
- **[📘 DTO Usage Guide](docs/DTO_USAGE_GUIDE.md)** - How to use DTOs in your Laravel application
- **[🎯 Examples Collection](examples/README.md)** - Working examples and templates

### Core Concepts
- **[YAML Schema](docs/YAML_SCHEMA.md)** - Full YAML configuration reference
- **[Field Types](docs/FIELD_TYPES.md)** - All available field types and options
- **[Traits Guide](docs/TRAITS_GUIDE.md)** - Functional and behavioral traits system
- **[Validation Rules](docs/VALIDATION_RULES.md)** - Custom validation and error handling

### Advanced Features
- **[Collection Management](docs/COLLECTION_MANAGEMENT.md)** - Working with DTO collections and API resources
- **[Export Formats](docs/EXPORT_FORMATS.md)** - Export DTOs in 10 different formats
- **[Field Transformers](docs/FIELD_TRANSFORMERS.md)** - Automatic field value transformation
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
- **[Export Formats](examples/export-formats-example.php)** - Export DTOs in 10 different formats
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