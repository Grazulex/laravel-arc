# Changelog

All notable changes to `laravel-arc` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

*No unreleased changes*

## [1.2.0] - 2025-06-14

### 🚀 Major Feature: Intelligent DTO Generation with Artisan Command

#### Added
- **🛠️ New Artisan Command**: `php artisan make:dto` for automatic DTO generation
  - **Basic generation**: `php artisan make:dto User`
  - **Model-based generation**: `php artisan make:dto User --model=User`
  - **Custom paths**: `php artisan make:dto Product --path=app/DTOs`
  - **Intelligent analysis**: Automatic property extraction from Eloquent models

- **🧠 Multi-Layered Type Detection**: Intelligent type detection with prioritized sources
  - **🎯 Model Casts Analysis** (Highest Priority): Reads `$casts` property for exact type mapping
    - `'age' => 'integer'` → `public int $age` (non-nullable)
    - `'is_active' => 'boolean'` → `public bool $is_active`
    - `'metadata' => 'array'` → `public ?array $metadata`
    - `'salary' => 'decimal:2'` → `public float $salary`
    - `'created_at' => 'datetime'` → `#[DateProperty] public ?Carbon $created_at`
  
  - **🗃️ Database Schema Inspection** (Second Priority): Analyzes actual column types and constraints
    - `VARCHAR` → `string`, `INT` → `int`, `DECIMAL` → `float`
    - `NULL`/`NOT NULL` → Nullable/Non-nullable properties
    - Real-time database introspection for accurate types
  
  - **📄 Migration File Parsing** (Third Priority): Extracts types from Laravel migration files
    - `$table->string('name')` → `public ?string $name`
    - `$table->integer('age')->nullable()` → `public ?int $age`
    - `$table->boolean('is_active')` → `public bool $is_active`
    - `$table->timestamp('created_at')` → `#[DateProperty] public ?Carbon $created_at`
  
  - **🔍 Smart Pattern Matching** (Fallback): Intelligent pattern-based type detection
    - `*_id`, `id` → `int`
    - `*_at` → `Carbon` with `DateProperty`
    - `email` → `string` with validation `email`
    - `password` → `string`
    - `phone` → `string` nullable
    - `*url*` → `string` with validation `url`
    - `price`, `amount`, `cost` → `float`
    - `count`, `quantity`, `number` → `int`
    - `is_*`, `has_*` → `bool` with default `false`

- **⚡ Automatic Features**:
  - **📋 Property Extraction**: Automatic analysis of `fillable` attributes
  - **📅 Date Handling**: Uses `DateProperty` for timestamp fields with proper Carbon typing
  - **✅ Validation Rules**: Generates appropriate validation rules based on detected types
  - **🛡️ Safety First**: Prevents overwriting existing files
  - **📂 Directory Creation**: Creates target directories automatically if they don't exist
  - **🏷️ Suffix Management**: Automatically adds "DTO" suffix if not present

#### Enhanced
- **🔧 Enhanced ArcServiceProvider**: Updated with command registration
- **📚 Comprehensive Documentation**: Complete command documentation in README
- **🎨 Code Generation**: Smart namespace detection and PHP code generation

#### Quality & Developer Experience
- **📊 Standardization**: All example files renamed to consistent PascalCase naming
  - `simple_example.php` → `SimpleExample.php`
  - `usage_example.php` → `UsageExample.php`
  - `demo_advanced_features.php` → `DemoAdvancedFeatures.php`
  - `enum_simple_example.php` → `EnumSimpleExample.php`
  - `enum_advanced_example.php` → `EnumAdvancedExample.php`
  - `factory_example.php` → `FactoryExample.php`
  - `team_example.php` → `TeamExample.php`

- **📚 Enhanced Documentation**:
  - Updated README with comprehensive command documentation
  - Improved examples table with proper formatting
  - Added new examples: `AdvancedModelDTO.php`, `ProductDTO.php`
  - Multi-layered type detection explanation

- **🧪 Comprehensive Testing**: **86 tests** with **327 assertions**
  - New test suites: `MakeDtoCommandTest`, `MakeDtoAdvancedTest`, `MakeDtoWithModelTest`
  - Complete coverage of command functionality
  - Model analysis testing with mock models
  - Edge case handling and error scenarios

- **🏆 Code Quality**: Maintained excellence
  - **PHPStan Level 6**: Zero errors with strict type safety
  - **PSR-12 Compliance**: All code properly formatted
  - **100% Backward Compatibility**: No breaking changes

#### Usage Examples
```bash
# Generate basic DTO
php artisan make:dto User

# Generate from model with intelligent type detection
php artisan make:dto User --model=User

# Custom path and model analysis
php artisan make:dto Product --model=Product --path=app/DTOs
```

#### Generated DTO Example
For a User model with casts:
```php
protected $casts = [
    'age' => 'integer',
    'is_active' => 'boolean',
    'metadata' => 'array',
];
```

Generates:
```php
class UserDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: false, validation: 'email')]
    public ?string $email;

    #[Property(type: 'int', required: true)]  // From cast
    public int $age;

    #[Property(type: 'bool', required: true)]  // From cast
    public bool $is_active;

    #[Property(type: 'array', required: false)]  // From cast
    public ?array $metadata;

    #[DateProperty(required: false)]
    public ?Carbon $created_at;
}
```

## [1.1.0] - 2025-06-14

### 🆕 Major Feature: PHP Enums Support

#### Added
- **🎯 Complete PHP 8.1+ Enums Integration**: Full support for modern PHP enums with automatic casting
  - **New `EnumProperty` attribute**: Specialized attribute for enum properties with type safety
  - **Automatic enum casting**: Convert strings/integers to enum instances seamlessly
    - `'active'` → `UserStatus::ACTIVE` (BackedEnum)
    - `'ADMIN'` → `UserRole::ADMIN` (UnitEnum)
  - **Dual enum type support**:
    - **BackedEnum**: Enums with values (`enum Status: string { case ACTIVE = 'active'; }`)
    - **UnitEnum**: Pure enums (`enum Role { case ADMIN; case USER; }`)
  - **Smart serialization**: Automatic conversion back to original values
    - BackedEnum serializes to value (`UserStatus::ACTIVE` → `'active'`)
    - UnitEnum serializes to name (`UserRole::ADMIN` → `'ADMIN'`)
  - **Default enum values**: Support for default enum instances in DTOs
  - **Type safety**: Full validation and type checking for enum properties
  - **Direct property access**: `$user->status = UserStatus::PENDING`

#### Enhanced
- **🔧 Extended CastManager**: New enum casting methods with error handling
  - `castToEnum()`: Convert values to enum instances with validation
  - `serializeEnum()`: Smart serialization based on enum type
  - Full integration with existing casting system
- **📊 Enhanced DTOTrait**: Extended reflection system to support EnumProperty
- **🎨 Improved Type System**: Better validation for enum types in property assignment

#### Documentation & Examples
- **📚 Comprehensive Documentation**: Complete enum section in README with real-world examples
- **💼 Practical Examples**: 
  - `EnumExampleDTO.php`: Complete enum DTO demonstration
  - `enum_simple_example.php`: Simple enum usage without Laravel dependencies
  - `enum_advanced_example.php`: Advanced enum usage with business logic
- **🧪 Complete Test Suite**: 6 comprehensive feature tests covering all enum functionality
  - String to enum casting tests
  - Direct enum instance handling
  - Default value behavior
  - Serialization accuracy
  - Property modification
  - JSON conversion

#### Code Quality
- **✅ Zero PHPStan errors**: All new code passes Level 6 static analysis
- **✅ PSR-12 compliant**: All code properly formatted and standardized
- **✅ 100% test coverage**: All enum features thoroughly tested
- **✅ Production ready**: Full error handling and edge case management

#### Usage Example
```php
// Define enums
enum UserStatus: string { case ACTIVE = 'active'; case PENDING = 'pending'; }
enum UserRole { case ADMIN; case USER; }

// Use in DTOs
class UserDTO extends LaravelArcDTO {
    #[EnumProperty(enumClass: UserStatus::class, required: true)]
    public UserStatus $status;
    
    #[EnumProperty(enumClass: UserRole::class, default: UserRole::USER)]
    public UserRole $role;
}

// Automatic casting
$user = new UserDTO(['status' => 'active', 'role' => 'ADMIN']);
echo $user->status->value; // 'active'
echo $user->role->name;    // 'ADMIN'
```

## [1.0.0] - 2025-06-13

### 🎉 Initial Release

#### Added
- **🎯 Direct Property Access**: No more getters/setters - access DTO properties directly (`$user->name`)
- **⚡ Automatic Validation**: Validation based on PHP 8+ attributes with Laravel integration
- **🔧 Advanced Features**:
  - **Carbon Date Transformation**: Automatic conversion with timezone support
  - **Nested DTOs**: Embed DTOs within other DTOs seamlessly
  - **Advanced Casting System**: Extensible data transformation pipeline
  - **DTO Factory/Builder Pattern**: Generate test data and prototypes easily
- **🛡️ Type Safety**: Real-time type validation with detailed error messages
- **📊 Laravel Integration**: Automatic validation rules generation
- **🧪 Comprehensive Testing**: Complete test suite using Pest
- **📚 Complete Documentation**: Examples and real-world usage patterns

#### Features
- `LaravelArcDTO` base class for creating DTOs
- `Property`, `DateProperty`, `NestedProperty` attributes
- `ArcServiceProvider` for Laravel integration
- `DTOInterface` and `DTOTrait` for extensibility
- Magic methods for backward compatibility
- Array and JSON conversion methods
- Advanced casting with `CastManager`
- Factory pattern for test data generation

#### Requirements
- PHP 8.2+
- Laravel 11+ | 12+
- Carbon 3.10+

