# Laravel Arc

<img src="new_logo.png" alt="Laravel Arc" width="200">

Generate modern, type-safe Data Transfer Objects (DTOs) in Laravel from clean YAML definitions — with automatic validation, nested support, and fluent collection handling.

[![Latest Version](https://img.shields.io/packagist/v/grazulex/laravel-arc.svg?style=flat-square)](https://packagist.org/packages/grazulex/laravel-arc)
[![Total Downloads](https://img.shields.io/packagist/dt/grazulex/laravel-arc.svg?style=flat-square)](https://packagist.org/packages/grazulex/laravel-arc)
[![License](https://img.shields.io/github/license/grazulex/laravel-arc.svg?style=flat-square)](https://github.com/Grazulex/laravel-arc/blob/main/LICENSE.md)
[![PHP Version](https://img.shields.io/packagist/php-v/grazulex/laravel-arc.svg?style=flat-square)](https://php.net/)
[![Laravel Version](https://img.shields.io/badge/laravel-12.x%20%7C%2013.x-ff2d20?style=flat-square&logo=laravel)](https://laravel.com/)
[![Tests](https://img.shields.io/github/actions/workflow/status/grazulex/laravel-arc/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/Grazulex/laravel-arc/actions)
[![Code Style](https://img.shields.io/badge/code%20style-pint-000000?style=flat-square&logo=laravel)](https://github.com/laravel/pint)

## Overview

Laravel Arc is a powerful Laravel package that simplifies Data Transfer Object (DTO) management through YAML-driven generation. Define your DTOs in simple YAML files and let Laravel Arc generate type-safe, validated PHP classes with automatic property access and comprehensive collection support.

**Think of it as Laravel API Resources, but with stronger typing, automatic validation, and generated from YAML definitions.**

### 🎯 Key Features

- **🏗️ YAML-Driven Generation** - Define DTOs in clean, readable YAML
- **🔒 Type Safety** - Full PHP 8.3+ type enforcement with readonly properties
- **✅ Automatic Validation** - Generate Laravel validation rules from field definitions
- **� ModelSchema Integration** - 65+ advanced field types (geometric, JSON, enhanced validation)
- **�🔄 Field Transformers** - Built-in data transformation (trim, slugify, normalize, etc.)
- **📊 Export Formats** - Convert to JSON, XML, CSV, YAML, and more
- **🎯 Behavioral Traits** - Timestamps, UUIDs, soft deletes, and tagging
- **🚀 Modern PHP** - Leverages PHP 8.3+ features and best practices

### 🔧 Advanced Field Types (ModelSchema Integration)

Laravel Arc integrates with **grazulex/laravel-modelschema** to provide **65+ advanced field types**:

```yaml
# Traditional Arc types
fields:
  name:
    type: string
  age:
    type: integer

# Advanced ModelSchema types  
fields:
  coordinates:
    type: point          # Geographic point
  boundary:
    type: polygon        # Geographic polygon
  metadata:
    type: json           # JSON with validation
  tags:
    type: set            # Set collection
  email:
    type: email          # Enhanced email validation
  settings:
    type: jsonb          # PostgreSQL JSONB
```

**Supported Advanced Types:**
- **🌍 Geometric**: `point`, `polygon`, `geometry`, `linestring`
- **📋 JSON**: `json`, `jsonb`, `set`, `array`
- **📧 Enhanced String**: `email`, `uuid`, `url`, `slug`, `phone`
- **🔢 Numeric Variations**: `bigint`, `tinyint`, `decimal`, `money`
- **📅 Date/Time**: `datetime`, `timestamp`, `date`, `time`

### 📚 Complete Documentation

**➡️ [Visit the Wiki for complete documentation, examples, and guides](https://github.com/Grazulex/laravel-arc/wiki)**

The wiki contains:
- **[Installation & Setup](https://github.com/Grazulex/laravel-arc/wiki/Installation-Setup)** 
- **[Your First DTO](https://github.com/Grazulex/laravel-arc/wiki/Your-First-DTO)**
- **[Field Types](https://github.com/Grazulex/laravel-arc/wiki/Field-Types)**
- **[Field Transformers](https://github.com/Grazulex/laravel-arc/wiki/Field-Transformers)**
- **[Artisan Commands](https://github.com/Grazulex/laravel-arc/wiki/Artisan-Commands)**
- **[Complete Examples](https://github.com/Grazulex/laravel-arc/wiki/Complete-Examples)**

## 📦 Quick Installation

```bash
composer require grazulex/laravel-arc
php artisan vendor:publish --provider="Grazulex\LaravelArc\LaravelArcServiceProvider"
```

## 🚀 Quick Start

1. **Create a DTO definition:**
```bash
php artisan dto:definition-init UserDTO --model=App\\Models\\User --table=users
```

2. **Generate the DTO class:**
```bash
php artisan dto:generate user.yaml
```

3. **Use your DTO:**
```php
$userData = ['name' => 'John Doe', 'email' => 'john@example.com'];
$userDto = UserDTO::fromArray($userData);

echo $userDto->name; // 'John Doe'
echo $userDto->toJson(); // JSON representation
```

## ⚠️ Important Notes

### YAML Validation Rules with Commas
When using validation rules that contain commas (like `exists:table,column`), wrap them in quotes:

```yaml
# ❌ Wrong - gets split into separate rules
rules: [required, exists:users,id]

# ✅ Correct - stays as one rule  
rules: [required, "exists:users,id"]
```

This applies to rules like: `"exists:table,column"`, `"unique:table,column"`, `"in:value1,value2,value3"`, etc.

## 📖 Learn More

- **[📚 Complete Documentation](https://github.com/Grazulex/laravel-arc/wiki)** - Full guides and API reference
- **[🚀 Installation & Setup](https://github.com/Grazulex/laravel-arc/wiki/Installation-Setup)** - Installation and first steps
- **[💡 Complete Examples](https://github.com/Grazulex/laravel-arc/wiki/Complete-Examples)** - Real-world usage examples
- **[🔧 Behavioral Traits](https://github.com/Grazulex/laravel-arc/wiki/Behavioral-Traits)** - Advanced features and traits

## 🔧 Requirements

- **PHP:** 8.3+
- **Laravel:** 12.x or 13.x
- **Carbon:** ^3.10
- **grazulex/laravel-modelschema:** ^1.2

## 🧪 Testing

```bash
composer test
```

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## 🔒 Security

Please review our [Security Policy](SECURITY.md) for reporting vulnerabilities.

## 📄 License

Laravel Arc is open-sourced software licensed under the [MIT license](LICENSE.md).

---

**Made with ❤️ by [Jean-Marc Strauven](https://github.com/Grazulex)**