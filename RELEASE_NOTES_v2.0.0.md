# 🚀 Laravel Arc v2.0.0: Advanced Features Release

This major release introduces powerful new capabilities while maintaining the simple and elegant API that makes Laravel Arc special.

## 🌟 What's New

### 🗓️ Automatic Carbon Date Transformation
```php
#[DateProperty(required: false, format: 'Y-m-d', timezone: 'Europe/Brussels')]
public ?Carbon $birthDate;

$user = new UserDTO([
    'birthDate' => '1990-05-15',    // String -> Carbon automatically
    'createdAt' => time()           // Unix timestamp -> Carbon
]);

echo $user->birthDate->format('d/m/Y'); // 15/05/1990
```

### 🏗️ Nested DTOs & Collections
```php
#[NestedProperty(dtoClass: AddressDTO::class)]
public ?AddressDTO $address;

#[NestedProperty(dtoClass: UserDTO::class, isCollection: true)]
public array $members;

$user = new UserDTO([
    'address' => ['street' => '123 Main St', 'city' => 'Brussels']
]);

echo $user->address->street; // Direct access to nested properties!
```

### ⚙️ Advanced Casting System
- Extensible `CastManager` for custom transformations
- Automatic bidirectional conversion (input ↔ output)
- Enhanced error handling with detailed exceptions
- Support for complex data structures

## ✨ Key Features

- **🔄 Seamless Date Handling**: Parse strings, timestamps, or Carbon instances automatically
- **📦 Complex Data Structures**: Nest DTOs within DTOs with array-to-DTO conversion
- **🔢 Collections Support**: Handle arrays of nested DTOs effortlessly
- **🌍 Timezone Support**: Full timezone handling for international applications
- **🎯 Type Safety**: Enhanced type checking with casting support
- **🔍 Better Debugging**: Improved error messages and exception handling
- **📤 Smart Serialization**: Automatic formatting when converting to arrays/JSON

## 📚 Examples & Documentation

- **Interactive Demo**: `src/Examples/demo_advanced_features.php` - See all features in action
- **Complex Examples**: `src/Examples/AdvancedExampleDTO.php` - Real-world usage patterns
- **Updated README**: Comprehensive documentation with examples
- **Test Suite**: 13 new tests ensuring reliability (55 total tests, 192 assertions)

## 🔧 Technical Details

### New Files Added
- `src/Attributes/DateProperty.php` - Date-specific attribute
- `src/Attributes/NestedProperty.php` - Nested DTO attribute
- `src/Casting/CastManager.php` - Advanced casting system
- `tests/Unit/AdvancedFeaturesTest.php` - Comprehensive test coverage

### Dependencies
- Added `nesbot/carbon` for advanced date handling
- Compatible with Carbon v3.10+
- Maintains all existing Laravel compatibility

### Backward Compatibility
- ✅ **100% backward compatible** - all existing code continues to work
- ✅ **No breaking changes** - upgrade safely from v1.x
- ✅ **Same simple API** - enhanced, not complicated

## 🚀 Quick Start

```bash
composer require grazulex/laravel-arc:^2.0
```

```php
use Grazulex\Arc\LaravelArcDTO;
use Grazulex\Arc\Attributes\{DateProperty, NestedProperty};
use Carbon\Carbon;

class UserDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $name;
    
    #[DateProperty(required: false, format: 'Y-m-d')]
    public ?Carbon $birthDate;
    
    #[NestedProperty(dtoClass: AddressDTO::class, required: false)]
    public ?AddressDTO $address;
}

// Usage
$user = new UserDTO([
    'name' => 'Jean-Marc',
    'birthDate' => '1990-05-15',
    'address' => ['street' => '123 Main St', 'city' => 'Brussels']
]);

// Direct access with automatic conversion
echo $user->birthDate->format('d/m/Y');
echo $user->address->city;
```

## 🎯 Perfect For

- **API Development**: Handle complex JSON payloads with nested structures
- **Data Processing**: Transform dates and nested data automatically
- **Form Handling**: Validate and structure form data with relationships
- **International Apps**: Full timezone and date format support
- **Enterprise Apps**: Type-safe data handling with comprehensive validation

## 🔗 Links

- [Documentation](https://github.com/grazulex/laravel-arc#readme)
- [Packagist](https://packagist.org/packages/grazulex/laravel-arc)
- [Issues](https://github.com/grazulex/laravel-arc/issues)
- [Contributing](https://github.com/grazulex/laravel-arc/blob/main/CONTRIBUTING.md)

---

**Full Changelog**: https://github.com/grazulex/laravel-arc/compare/v1.4.0...v2.0.0

