# 🎉 Laravel Arc v1.0.0 - First Stable Release

Welcome to **Laravel Arc v1.0.0**! This marks the official stable release of our elegant and modern Data Transfer Objects (DTOs) package for Laravel.

## 🚀 What is Laravel Arc?

Laravel Arc provides a powerful yet simple way to work with Data Transfer Objects in Laravel applications, featuring direct property access, automatic validation, and modern PHP 8+ attributes.

## ✨ Key Features

### 🎯 **Direct Property Access**
No more getters/setters! Access your DTO properties directly:
```php
$user = new UserDTO(['name' => 'John', 'email' => 'john@example.com']);
echo $user->name; // John
$user->age = 30;
```

### ✅ **Automatic Validation**
Define validation rules with PHP attributes:
```php
#[Property(type: 'string', required: true, validation: 'email')]
public string $email;

// Get Laravel validation rules automatically
$rules = UserDTO::rules();
```

### 🔧 **Advanced Features**
- **Nested DTOs**: Embed DTOs within other DTOs
- **Collections**: Handle arrays of DTOs seamlessly
- **Date Properties**: Automatic Carbon transformation with timezone support
- **Advanced Casting**: Custom type casting system
- **Factory Pattern**: DTO Factory/Builder support
- **Type Safety**: Real-time type validation
- **Default Values**: Set default values with attributes

## 📦 Installation

```bash
composer require grazulex/laravel-arc
```

## 🎯 Quick Start Example

```php
use Grazulex\Arc\LaravelArcDTO;
use Grazulex\Arc\Attributes\Property;

class UserDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true, validation: 'max:255')]
    public string $name;

    #[Property(type: 'string', required: true, validation: 'email')]
    public string $email;

    #[Property(type: 'integer', required: true, validation: 'min:0|max:150')]
    public int $age;

    #[Property(type: 'string', required: false, default: 'user')]
    public string $role;
}

// Usage
$user = new UserDTO([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30
]);

// Direct access
echo $user->name;     // John Doe
echo $user->role;     // user (default value)

// Automatic validation rules
$rules = UserDTO::rules();
// Returns: ['name' => 'required|string|max:255', 'email' => 'required|string|email', ...]
```

## 🔗 Requirements

- **PHP**: 8.2+
- **Laravel**: 11.0+ | 12.0+
- **Carbon**: 3.10+

## 📚 Documentation

For complete documentation, examples, and advanced usage, visit our [GitHub repository](https://github.com/grazulex/laravel-arc).

## 🐛 Reporting Issues

Found a bug? Have a feature request? Please [open an issue](https://github.com/grazulex/laravel-arc/issues) on GitHub.

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](https://github.com/grazulex/laravel-arc/blob/main/CONTRIBUTING.md) for details.

## 📄 License

Laravel Arc is open-sourced software licensed under the [MIT license](https://github.com/grazulex/laravel-arc/blob/main/LICENSE.md).

---

**Happy coding with Laravel Arc! 🚀**

*Built with ❤️ by [Jean-Marc Strauven](https://github.com/Grazulex)*

