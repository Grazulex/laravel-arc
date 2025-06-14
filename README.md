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

### 🎯 **Core Features (v2.0)**
- ✅ **Direct property access** (`$user->name` instead of `$user->getName()`)
- ✅ **Unified Property syntax** - Single `Property` attribute for all types
- ✅ **Automatic validation** based on PHP 8+ attributes
- ✅ **Real-time type validation** with detailed exceptions
- ✅ **Laravel integration** with automatic validation rules generation

### 🆕 **Advanced Features**
- 🔥 **PHP 8.1+ Enums Support** - Full enum integration with automatic casting
- 📅 **Carbon Date Transformation** - Automatic date handling with timezone support
- 🏗️ **Nested DTOs & Collections** - Compose complex data structures
- 🎨 **Advanced Casting System** - Extensible data transformation pipeline
- 🏭 **DTO Factory/Builder Pattern** - Generate test data and prototypes
- 🔄 **Transformation Pipeline** - Pre-processing data before casting
- 🔍 **Auto-Discovery Relations** - Automatic Eloquent relation detection
- 🛡️ **Smart Validation Rules** - Intelligent validation generation

### 🛡️ **Developer Experience & Tools**
- 🎯 **Type Safety** - Full compile-time and runtime validation (PHPStan Level 6)
- 📝 **Default Values** - Smart property initialization
- 🚀 **Simple API** - Intuitive and clean interface
- 🧪 **Test-Friendly** - Built-in factory for test data generation
- 🔧 **Debug Tools** - `dto:analyze` and `dto:validate` commands
- 📊 **IDE Integration** - Enhanced autocompletion and refactoring

## 📦 Installation

```bash
composer require grazulex/laravel-arc
```

## 🛠️ Artisan Command

Laravel Arc includes a powerful Artisan command to generate DTOs automatically:

```bash
# Create a basic DTO
php artisan make:dto User

# Create a DTO from an existing model
php artisan make:dto User --model=User

# Create a DTO in a custom directory
php artisan make:dto Product --path=app/DTOs

# Combine model analysis with custom path
php artisan make:dto Product --model=Product --path=app/Data
```

### Command Features

- 🧠 **Intelligent Type Detection** - Multi-layered approach for precise type detection
  - 🎯 **Model Casts Analysis** - Reads `$casts` property for exact type mapping
  - 🗃️ **Database Schema Inspection** - Analyzes actual column types and nullable constraints
  - 📄 **Migration File Parsing** - Extracts types from Laravel migration files
  - 🔍 **Smart Pattern Matching** - Fallback based on naming conventions
- ✅ **Smart Model Analysis** - Automatically extracts properties from Eloquent models
- ✅ **Validation Rules** - Generates appropriate validation rules automatically
- ✅ **Date Handling** - Uses `DateProperty` for timestamp fields with proper Carbon typing
- ✅ **Directory Creation** - Creates target directories if they don't exist
- ✅ **Conflict Prevention** - Prevents overwriting existing files
- ✅ **Flexible Paths** - Supports custom namespace and directory structures

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
- **`class`** : Target class for `enum`, `nested`, or `collection` types

### 🎨 Clean Type-Based Syntax

Laravel Arc uses a modern, consistent syntax for all property types:

```php
// Basic types
#[Property(type: 'string', required: true, validation: 'max:255')]
public string $name;

#[Property(type: 'int', required: true, validation: 'min:0')]
public int $age;

// Complex types - clear and explicit
#[Property(type: 'enum', class: UserStatus::class, required: true)]
public UserStatus $status;

#[Property(type: 'date', format: 'Y-m-d', timezone: 'UTC')]
public ?Carbon $birthDate;

#[Property(type: 'nested', class: AddressDTO::class, required: false)]
public ?AddressDTO $address;

#[Property(type: 'collection', class: UserDTO::class, required: false)]
public array $members;
```

#### 🎯 **Benefits**

- ✅ **No redundancy** - specify the class only once
- ✅ **Clear intent** - explicit type keywords (`enum`, `date`, `nested`, `collection`)
- ✅ **Consistent pattern** - same syntax for all complex types
- ✅ **Better readability** - easier to understand at a glance
- ✅ **IDE friendly** - better autocomplete and validation
- ✅ **Future-proof** - aligned with modern PHP practices

## 🎨 Advanced Features

### Date Properties with Carbon

Automatic transformation of dates to Carbon instances:

```php
use Grazulex\Arc\Attributes\Property;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class UserDTO extends LaravelArcDTO
{
    #[Property(type: 'date', required: false, format: 'Y-m-d', timezone: 'Europe/Brussels')]
    public ?Carbon $birthDate;

    #[Property(type: 'date', required: false, immutable: true)]
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
use Grazulex\Arc\Attributes\Property;

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
    
    #[Property(type: 'nested', class: AddressDTO::class, required: false)]
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
use Grazulex\Arc\Attributes\Property;

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

    #[Property(type: 'enum', class: UserStatus::class, required: true)]
    public UserStatus $status;

    #[Property(type: 'enum', class: UserRole::class, default: UserRole::USER)]
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
    
    #[Property(type: 'collection', class: UserDTO::class, required: false)]
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

### 🔄 Transformation Pipeline (NEW in v2.0)

Apply data transformations before casting with the new transformation pipeline:

```php
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Transformers\TrimTransformer;
use Grazulex\Arc\Transformers\LowercaseTransformer;
use Grazulex\Arc\Transformers\UppercaseTransformer;
use Grazulex\Arc\Transformers\HashTransformer;

class UserDTO extends LaravelArcDTO
{
    // Email: trim whitespace and convert to lowercase
    #[Property(type: 'string', required: true, transform: [TrimTransformer::class, LowercaseTransformer::class])]
    public string $email;
    
    // Country code: trim and convert to uppercase
    #[Property(type: 'string', required: false, transform: [TrimTransformer::class, UppercaseTransformer::class])]
    public ?string $country_code;
    
    // Password: hash the value (for security)
    #[Property(type: 'string', required: true, transform: [HashTransformer::class])]
    public string $password_hash;
}

$user = new UserDTO([
    'email' => '  TEST@EXAMPLE.COM  ',     // Becomes: 'test@example.com'
    'country_code' => '  us  ',             // Becomes: 'US'
    'password_hash' => 'mypassword123'      // Becomes: hashed value
]);
```

#### Available Transformers:
- **TrimTransformer** - Removes whitespace
- **LowercaseTransformer** - Converts to lowercase
- **UppercaseTransformer** - Converts to uppercase
- **HashTransformer** - Hashes values (configurable algorithm)
- **Custom Transformers** - Implement `TransformerInterface` for custom logic

### 🔍 Auto-Discovery Relations (NEW in v2.0)

Automatically detect and generate DTO properties from Eloquent model relations:

```bash
# Generate DTO with all relations
php artisan make:dto User --model=User --with-relations

# Generate DTO with specific relations only
php artisan make:dto Order --model=Order --relations=user,items
```

This automatically generates:
```php
class UserDTO extends LaravelArcDTO {
    // Regular model properties
    #[Property(type: 'string', required: true)]
    public string $name;
    
    // Auto-detected relations
    // Relation: collection -> Order
    #[Property(type: 'collection', class: OrderDTO::class, required: false)]
    public array $orders;
    
    // Relation: single -> Profile
    #[Property(type: 'nested', class: ProfileDTO::class, required: false)]
    public ?ProfileDTO $profile;
}
```

### 🛡️ Smart Validation Rules (NEW in v2.0)

Intelligent validation rule generation based on field names and types:

```bash
# Generate DTO with smart validation
php artisan make:dto User --model=User --with-validation

# Generate with strict validation rules
php artisan make:dto User --model=User --with-validation --validation-strict
```

Automatically generates appropriate rules:
```php
class UserDTO extends LaravelArcDTO {
    #[Property(type: 'string', required: true, validation: 'required|email|max:254')]
    public string $email;
    
    #[Property(type: 'string', required: true, validation: 'required|min:8|regex:/strong_password/')]
    public string $password;
    
    #[Property(type: 'int', required: true, validation: 'required|integer|min:0|max:150')]
    public int $age;
    
    #[Property(type: 'string', required: false, validation: 'nullable|regex:/phone_format/')]
    public ?string $phone;
}
```

### 🔧 Debug & Analysis Tools (NEW in v2.0)

Powerful command-line tools for DTO analysis and debugging:

```bash
# Analyze DTO structure and configuration
php artisan dto:analyze UserDTO
php artisan dto:analyze UserDTO --json

# Validate data against DTO
php artisan dto:validate UserDTO --data='{"name":"John","email":"john@example.com"}'
php artisan dto:validate UserDTO --file=data.json

# Interactive validation
php artisan dto:validate UserDTO
> Enter JSON data: {"name": "John"}
> ✅ Validation passed!
```

#### Analysis Output Example:
```
📊 DTO Analysis: App\Data\UserDTO
📁 File: /app/Data/UserDTO.php
🏷️ Is DTO: ✅ Yes

📈 Statistics:
┌─────────────────────────────┬───────┐
│ Metric                      │ Count │
├─────────────────────────────┼───────┤
│ Total Properties            │ 5     │
│ DTO Properties              │ 5     │
│ Required Properties         │ 3     │
│ Properties with Validation  │ 2     │
│ Properties with Transform   │ 3     │
└─────────────────────────────┴───────┘
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
| [`ProductDTO.php`](src/Examples/ProductDTO.php) | Product DTO with various property types |
| [`SimpleExample.php`](src/Examples/SimpleExample.php) | Basic DTO usage patterns |
| [`UsageExample.php`](src/Examples/UsageExample.php) | General usage demonstration |
| **Advanced Features** | |
| [`AdvancedExampleDTO.php`](src/Examples/AdvancedExampleDTO.php) | Complex nested structures with dates |
| [`AdvancedModelDTO.php`](src/Examples/AdvancedModelDTO.php) | Generated DTO from model with casts |
| [`ModernUserDTO.php`](src/Examples/ModernUserDTO.php) | Modern DTO implementation |
| [`DemoAdvancedFeatures.php`](src/Examples/DemoAdvancedFeatures.php) | Practical demonstration |
| **Enums & Factories** | |
| [`EnumExampleDTO.php`](src/Examples/EnumExampleDTO.php) | Complete enum DTO demonstration |
| [`EnumSimpleExample.php`](src/Examples/EnumSimpleExample.php) | Simple enum usage |
| [`EnumAdvancedExample.php`](src/Examples/EnumAdvancedExample.php) | Advanced enum features |
| [`FactoryExample.php`](src/Examples/FactoryExample.php) | DTO Factory usage examples |
| [`TeamExample.php`](src/Examples/TeamExample.php) | Team and member relationships |

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

