# 🎨 Laravel Arc

<div align="center">
  <img src="logo-header.svg" alt="Laravel Arc Logo" width="420">
  
  *Elegant and modern Data Transfer Objects (DTOs) for Laravel*
</div>

<div align="center">

[![Latest Version](https://img.shields.io/packagist/v/grazulex/laravel-arc.svg?style=flat-square)](https://packagist.org/packages/grazulex/laravel-arc)
[![Total Downloads](https://img.shields.io/packagist/dt/grazulex/laravel-arc.svg?style=flat-square)](https://packagist.org/packages/grazulex/laravel-arc)
[![Tests](https://img.shields.io/github/actions/workflow/status/grazulex/laravel-arc/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/grazulex/laravel-arc/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/grazulex/laravel-arc?style=flat-square)](https://github.com/grazulex/laravel-arc/blob/main/LICENSE.md)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue?style=flat-square)](https://www.php.net/)
[![Laravel Version](https://img.shields.io/badge/Laravel-12%2B-blue?style=flat-square)](https://laravel.com/)


</div>

A Laravel package for elegant and modern Data Transfer Objects (DTOs) management with automatic validation, direct property access, and powerful advanced features.

## 🚀 Key Features

- ✅ **Direct property access** (`$user->name` instead of `$user->getName()`)
- ✅ **Unified Property syntax** - Single `Property` attribute for all types
- ✅ **Automatic validation** based on PHP 8+ attributes
- ✅ **PHP 8.1+ Enums Support** - Full enum integration with automatic casting
- ✅ **Advanced transformations** - Pre-process data before casting
- ✅ **Auto-discovery relations** - Automatic Eloquent relation detection
- ✅ **Smart validation rules** - Intelligent pattern-based validation
- ✅ **Debug & analysis tools** - `dto:analyze` and `dto:validate` commands

## 📦 Installation

```bash
composer require grazulex/laravel-arc
```

## 🎯 Quick Example

```php
use Grazulex\Arc\LaravelArcDTO;
use Grazulex\Arc\Attributes\Property;

class UserDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true, validation: 'max:255')]
    public string $name;

    #[Property(type: 'string', required: true, validation: 'email')]
    public string $email;

    #[Property(type: 'int', required: true, validation: 'min:0|max:150')]
    public int $age;
}

// Create and use
$user = new UserDTO([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30
]);

echo $user->name; // Direct property access!
$user->age = 31;  // Direct assignment with validation!
```

## 🛠️ Artisan Command

Generate DTOs automatically:

```bash
# Basic DTO generation
php artisan make:dto User

# Generate from existing model with intelligent type detection
php artisan make:dto User --model=User

# With relations and smart validation
php artisan make:dto User --model=User --with-relations --with-validation
```

## 🎨 Advanced Features

### Transformation Pipeline
```php
#[Property(
    type: 'string',
    required: true,
    transform: [TrimTransformer::class, LowercaseTransformer::class]
)]
public string $email; // '  TEST@EXAMPLE.COM  ' becomes 'test@example.com'
```

### Enum Support
```php
#[Property(type: 'enum', class: UserStatus::class)]
public UserStatus $status; // 'active' becomes UserStatus::ACTIVE
```

### Nested DTOs & Collections
```php
#[Property(type: 'nested', class: AddressDTO::class)]
public AddressDTO $address;

#[Property(type: 'collection', class: OrderItemDTO::class)]
public array $items;
```

### Debug Tools
```bash
# Analyze DTO structure
php artisan dto:analyze UserDTO

# Validate data against DTO
php artisan dto:validate UserDTO --data='{"name":"John"}'
```

## 📚 Documentation

**🚀 For complete documentation, examples, and advanced usage, visit our [Wiki](https://github.com/Grazulex/laravel-arc/wiki)!**

### Quick Links
- **[Quick Start Guide](https://github.com/Grazulex/laravel-arc/wiki/Quick-Start)** - Get up and running in 5 minutes
- **[MakeDtoCommand](https://github.com/Grazulex/laravel-arc/wiki/MakeDtoCommand)** - Intelligent DTO generation from models
- **[Property Attributes](https://github.com/Grazulex/laravel-arc/wiki/Property-Attributes)** - Learn the unified Property syntax
- **[Advanced Features](https://github.com/Grazulex/laravel-arc/wiki/Advanced-Features)** - Explore all advanced capabilities
- **[Examples Gallery](https://github.com/Grazulex/laravel-arc/wiki/Examples)** - Real-world usage examples
- **[Migration Guide](https://github.com/Grazulex/laravel-arc/wiki/Migration-v1-to-v2)** - Upgrade from v1 to v2

## 📋 Requirements

| Requirement | Version |
|-------------|-----|
| **PHP** | 8.3+ |
| **Laravel** | 12+ |
| **Carbon** | 3.10+ |

## 🔄 Migration from v1

Laravel Arc v2.0 introduced a unified `Property` attribute syntax. See our [Migration Guide](https://github.com/Grazulex/laravel-arc/wiki/Migration-v1-to-v2) for detailed upgrade instructions.

## 🧪 Testing

```bash
composer test        # Run all tests
composer test-coverage # Run with coverage
composer analyse     # PHPStan analysis
composer format      # Code formatting
```

## 🤝 Contributing

Contributions are welcome! Please see our [Contributing Guide](https://github.com/Grazulex/laravel-arc/wiki/Contributing) for details.

## 💫 Support

- **💬 [Discussions](https://github.com/Grazulex/laravel-arc/discussions)** - Community support
- **🐛 [Issues](https://github.com/Grazulex/laravel-arc/issues)** - Bug reports and feature requests
- **📚 [Wiki](https://github.com/Grazulex/laravel-arc/wiki)** - Complete documentation

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

**🚀 Ready to get started? Check out our [Quick Start Guide](https://github.com/Grazulex/laravel-arc/wiki/Quick-Start)!**

