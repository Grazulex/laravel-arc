# Laravel Arc

[![Latest Version](https://img.shields.io/packagist/v/grazulex/laravel-arc.svg?style=flat-square)](https://packagist.org/packages/grazulex/laravel-arc)
[![Total Downloads](https://img.shields.io/packagist/dt/grazulex/laravel-arc.svg?style=flat-square)](https://packagist.org/packages/grazulex/laravel-arc)
[![Tests](https://img.shields.io/github/actions/workflow/status/grazulex/laravel-arc/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/grazulex/laravel-arc/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/grazulex/laravel-arc?style=flat-square)](https://github.com/grazulex/laravel-arc/blob/main/LICENSE.md)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue?style=flat-square)](https://www.php.net/)

A Laravel package for elegant and modern Data Transfer Objects (DTOs) management with automatic validation and direct property access.

## 🚀 Features

- ✅ **Direct property access** (`$user->name` instead of `$user->getName()`)
- ✅ **Automatic validation** based on PHP 8+ attributes
- ✅ **Automatic Laravel validation rules generation**
- ✅ **Real-time type validation**
- ✅ **Default values**
- ✅ **Detailed exceptions**
- ✅ **Simple and intuitive API**

## 📦 Installation

```bash
composer require grazulex/laravel-arc
```

## 🎯 Quick Usage

### 1. Create a DTO

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
```

### 2. Use the DTO

```php
// Creation with automatic validation
$user = new UserDTO([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30
    // 'role' will have the default value 'user'
]);

// Direct property access - No more getters/setters!
echo $user->name;     // John Doe
echo $user->email;    // john@example.com
echo $user->age;      // 30
echo $user->role;     // user (default value)

// Direct assignment with type validation
$user->name = 'Jane Doe';
$user->age = 31;
$user->role = 'admin';

// Easy conversion
$array = $user->toArray();
$json = $user->toJson();
```

### 3. Automatic validation

```php
// Rules are generated automatically!
$rules = UserDTO::rules();
// Result:
// [
//     'name' => 'required|string|max:255',
//     'email' => 'required|string|email', 
//     'age' => 'required|integer|min:0|max:150',
//     'role' => 'nullable|string'
// ]
```

## 🔧 Property Attribute

The `Property` attribute allows you to define:

- **`type`** : Property type (`string`, `int`, `float`, `bool`, `array`, etc.)
- **`required`** : Whether the property is required (default: `true`)
- **`default`** : Default value if not provided
- **`validation`** : Additional Laravel validation rules

```php
#[Property(
    type: 'string',
    required: false,
    default: 'default_value',
    validation: 'max:100|regex:/^[a-zA-Z]+$/'
)]
public string $property;
```

## 🎨 Advanced Examples

See the `src/Examples/` folder for complete usage examples.

## 🤝 Compatibility

- PHP 8.2+
- Laravel 11+ / 12+

## Testing

This package uses [Pest](https://pestphp.com/) for testing with a comprehensive test suite covering all functionality.

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run only unit tests
composer test-unit

# Run only feature tests
composer test-feature

# Or use Pest directly
./vendor/bin/pest
```

### Test Structure

- **Unit Tests** (`tests/Unit/`): Test individual DTO components and functionality
  - `AttributeDTOTest.php`: Tests direct property access and attribute-based DTOs
  - `BasicDTOTest.php`: Tests basic DTO functionality
  - `SimpleDTOTest.php`: Tests simple DTO creation and usage
  - `ExampleDTOsTest.php`: Tests the example DTOs with real-world scenarios

- **Feature Tests** (`tests/Feature/`): Test complete workflows and integrations
  - `RealWorldUsageTest.php`: Tests real-world usage scenarios

### Example Test

```php
it('can create DTO and access properties directly', function () {
    $dto = new UserDTO([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'age' => 30
    ]);
    
    expect($dto->name)->toBe('John Doe');
    expect($dto->email)->toBe('john@example.com');
    expect($dto->age)->toBe(30);
});
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Jean-Marc Strauven](https://github.com/grazulex)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

