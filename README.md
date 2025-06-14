<div align="center">
  <img src="logo-header.svg" alt="Laravel Arc Logo" width="420">
  
  *Elegant and modern Data Transfer Objects (DTOs) for Laravel*
</div>

<div align="center">

[![Latest Version](https://img.shields.io/packagist/v/grazulex/laravel-arc.svg?style=flat-square)](https://packagist.org/packages/grazulex/laravel-arc)
[![Total Downloads](https://img.shields.io/packagist/dt/grazulex/laravel-arc.svg?style=flat-square)](https://packagist.org/packages/grazulex/laravel-arc)
[![Tests](https://img.shields.io/github/actions/workflow/status/grazulex/laravel-arc/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/grazulex/laravel-arc/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/grazulex/laravel-arc?style=flat-square)](https://github.com/grazulex/laravel-arc/blob/main/LICENSE.md)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue?style=flat-square)](https://www.php.net/)

</div>

A Laravel package for elegant and modern Data Transfer Objects (DTOs) management with automatic validation and direct property access.

---

## 📚 Table of Contents

- [🚀 Features](#features)
- [📦 Installation](#installation)
- [🎯 Quick Usage](#quick-usage)
- [🔧 Property Attribute](#property-attribute)
- [🎭 Advanced Features](#advanced-features)
  - [Date Properties with Carbon](#date-properties-with-carbon)
  - [Nested DTOs](#nested-dtos)
  - [PHP Enums Support (NEW)](#php-enums-support-new)
  - [Collections of DTOs](#collections-of-dtos)
  - [DTO Factory/Builder Pattern](#dto-factorybuilder-pattern)
- [🎫 Examples](#examples)
- [🤝 Compatibility](#compatibility)
- [🧪 Testing](#testing)
- [📊 Code Quality & Development](#code-quality--development)
- [📄 License](#license)

---

## 🚀 Features

### 🎯 **Core Features**
- ✅ **Direct property access** (`$user->name` instead of `$user->getName()`)
- ✅ **Automatic validation** based on PHP 8+ attributes
- ✅ **Real-time type validation** with detailed exceptions
- ✅ **Laravel integration** with automatic validation rules generation

### 🆕 **Advanced Features**
- 🔥 **PHP 8.1+ Enums Support** - Full enum integration with automatic casting
- 📅 **Carbon Date Transformation** - Automatic date handling with timezone support
- 🏗️ **Nested DTOs & Collections** - Compose complex data structures
- 🎨 **Advanced Casting System** - Extensible data transformation pipeline
- 🏭 **DTO Factory/Builder Pattern** - Generate test data and prototypes

### 🛡️ **Developer Experience**
- 🎯 **Type Safety** - Full compile-time and runtime validation
- 📝 **Default Values** - Smart property initialization
- 🚀 **Simple API** - Intuitive and clean interface
- 🧪 **Test-Friendly** - Built-in factory for test data generation

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

## 🎨 Advanced Features

### Date Properties with Carbon

Automatic transformation of dates to Carbon instances:

```php
use Grazulex\Arc\Attributes\DateProperty;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class UserDTO extends LaravelArcDTO
{
    #[DateProperty(required: false, format: 'Y-m-d', timezone: 'Europe/Brussels')]
    public ?Carbon $birthDate;

    #[DateProperty(required: false, immutable: true)]
    public ?CarbonImmutable $createdAt;
}

$user = new UserDTO([
    'birthDate' => '1990-05-15',           // String
    'createdAt' => time()                  // Unix timestamp
]);

// Automatic conversion to Carbon instances
echo $user->birthDate->format('d/m/Y');   // 15/05/1990
echo $user->createdAt->toDateTimeString(); // 2024-01-15 10:30:00

// Serialization back to strings
$array = $user->toArray();
// ['birthDate' => '1990-05-15', 'createdAt' => '2024-01-15 10:30:00']
```

### Nested DTOs

Embed DTOs within other DTOs:

```php
use Grazulex\Arc\Attributes\NestedProperty;

class AddressDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $street;
    
    #[Property(type: 'string', required: true)]
    public string $city;
}

class UserDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $name;
    
    #[NestedProperty(dtoClass: AddressDTO::class, required: false)]
    public ?AddressDTO $address;
}

$user = new UserDTO([
    'name' => 'John Doe',
    'address' => [
        'street' => '123 Main St',
        'city' => 'Brussels'
    ]
]);

// Direct access to nested properties
echo $user->address->street; // 123 Main St
$user->address->city = 'Antwerp';
```

### PHP Enums Support (NEW)

Laravel Arc now supports PHP 8.1+ enums with automatic casting:

```php
use Grazulex\Arc\Attributes\EnumProperty;

// Define your enums
enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
}

enum UserRole
{
    case ADMIN;
    case USER;
    case MODERATOR;
}

class UserDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $name;

    #[EnumProperty(enumClass: UserStatus::class, required: true)]
    public UserStatus $status;

    #[EnumProperty(enumClass: UserRole::class, default: UserRole::USER)]
    public UserRole $role;
}

// Usage with automatic enum casting
$user = new UserDTO([
    'name' => 'John Doe',
    'status' => 'active',      // Automatically converted to UserStatus::ACTIVE
    'role' => 'ADMIN'          // Automatically converted to UserRole::ADMIN
]);

// Direct access to enum instances
echo $user->status->value;     // 'active' (BackedEnum)
echo $user->role->name;        // 'ADMIN' (UnitEnum)

// Serialization back to original values
$array = $user->toArray();
// Result: ['name' => 'John Doe', 'status' => 'active', 'role' => 'ADMIN']
```

### Collections of DTOs

Handle arrays of nested DTOs:

```php
class TeamDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $name;
    
    #[NestedProperty(dtoClass: UserDTO::class, required: false, isCollection: true)]
    public array $members;
}

$team = new TeamDTO([
    'name' => 'Development Team',
    'members' => [
        ['name' => 'Alice', 'email' => 'alice@example.com'],
        ['name' => 'Bob', 'email' => 'bob@example.com']
    ]
]);

// Access collection members
foreach ($team->members as $member) {
    echo $member->name; // Each member is a UserDTO instance
}
```

### 🏭 DTO Factory/Builder Pattern

Generate test data and prototype DTOs with ease:

```php
// Quick fake data generation
$user = UserDTO::fake();
echo $user->name;  // "Lorem ipsum d3f2"
echo $user->email; // "alice123@example.com"
echo $user->age;   // 42

// Multiple instances
$users = UserDTO::fakeMany(5);

// Mix manual and fake data
$user = UserDTO::factory()
    ->with('name', 'Fixed Name')
    ->with('email', 'fixed@example.com')
    ->fake() // Generate fake data for other fields
    ->create();

// Batch attributes
$admin = UserDTO::factory()
    ->withAttributes([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'role' => 'admin'
    ])
    ->fakeOnly(['age']) // Only generate fake age
    ->create();

// Override with specific values
$testUser = UserDTO::fake(['role' => 'admin']);
```

#### Factory Features:

- **🎲 Smart fake data generation** based on property types and validation rules
- **📧 Realistic email generation** with random domains
- **📅 Automatic date generation** with timezone support
- **🏗️ Nested DTO creation** with automatic relationship building
- **📊 Collection support** for arrays of DTOs
- **🎯 Fluent builder API** for readable test data creation
- **⚡ Fast prototyping** for demos and development

#### Perfect for:

- **Unit Testing**: Generate consistent test data
- **Integration Testing**: Create complex object graphs
- **Demo Data**: Populate applications with realistic data
- **Prototyping**: Quickly build and test concepts
- **Seeding**: Database seeding with structured data

## 🎫 Examples

Explore complete usage examples in the [`src/Examples/`](src/Examples/) folder:

| Example | Description |
|---------|-------------|
| **Basic Usage** | |
| [`UserDTO.php`](src/Examples/UserDTO.php) | Basic user DTO example |
| [`simple_example.php`](src/Examples/simple_example.php) | Basic DTO usage patterns |
| [`usage_example.php`](src/Examples/usage_example.php) | General usage demonstration |
| **Advanced Features** | |
| [`AdvancedExampleDTO.php`](src/Examples/AdvancedExampleDTO.php) | Complex nested structures with dates |
| [`ModernUserDTO.php`](src/Examples/ModernUserDTO.php) | Modern DTO implementation |
| [`demo_advanced_features.php`](src/Examples/demo_advanced_features.php) | Practical demonstration |
| **Enums & Factories** | |
| [`EnumExampleDTO.php`](src/Examples/EnumExampleDTO.php) | Complete enum DTO demonstration |
| [`enum_simple_example.php`](src/Examples/enum_simple_example.php) | Simple enum usage |
| [`factory_example.php`](src/Examples/factory_example.php) | DTO Factory usage examples |
| [`team_example.php`](src/Examples/team_example.php) | Team and member relationships |

## 🤝 Compatibility

| Requirement | Version |
|-------------|----------|
| **PHP** | 8.2+ |
| **Laravel** | 11+ / 12+ |
| **Carbon** | 3.10+ |

## 🧪 Testing

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

## 📊 Code Quality & Development

Laravel Arc includes comprehensive tools for maintaining high code quality and development standards.

### Code Analysis

```bash
# Run PHPStan static analysis (Level 6)
composer analyse

# Run PHPStan directly with custom options
./vendor/bin/phpstan analyse --level=6 src/
```

### Code Formatting

```bash
# Format code with PHP CS Fixer
composer format

# Check formatting without making changes
composer format-check

# Run PHP CS Fixer directly
./vendor/bin/php-cs-fixer fix
```

### Quality Assurance Suite

```bash
# Run complete quality check (format check + analysis + tests)
composer quality
```

This command runs:
1. **Format Check**: Verifies code follows PSR standards
2. **Static Analysis**: PHPStan Level 6 analysis for type safety
3. **Test Suite**: Complete test coverage with Pest

### Development Workflow

For contributors and maintainers, the recommended workflow is:

```bash
# 1. Make your changes
vim src/SomeFile.php

# 2. Format the code
composer format

# 3. Run quality checks
composer quality

# 4. If all passes, commit your changes
git add .
git commit -m "feat: your feature description"
```

### Code Quality Standards

- **PSR-12** coding standards via PHP CS Fixer
- **PHPStan Level 6** static analysis for maximum type safety
- **100% PHPDoc coverage** for better IDE support
- **Comprehensive test coverage** with edge cases
- **Zero tolerance** for critical static analysis errors

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

