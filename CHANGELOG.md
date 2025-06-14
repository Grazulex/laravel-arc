# Changelog

All notable changes to `laravel-arc` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

*No unreleased changes*

## [2.0.0] - 2025-06-14

### 🚨 BREAKING CHANGES

#### ⚠️ Removed Legacy Attribute Classes
- **REMOVED**: `DateProperty` attribute class
- **REMOVED**: `EnumProperty` attribute class  
- **REMOVED**: `NestedProperty` attribute class
- **MIGRATION**: Use unified `Property` attribute with `type` parameter instead

#### 🔄 Migration Guide

**Before (v1.x - Legacy Syntax):**
```php
class UserDTO extends LaravelArcDTO {
    #[DateProperty(required: true)]
    public Carbon $created_at;
    
    #[EnumProperty(enumClass: UserStatus::class)]
    public UserStatus $status;
    
    #[NestedProperty(dtoClass: AddressDTO::class)]
    public AddressDTO $address;
}
```

**After (v2.0+ - Clean Syntax):**
```php
class UserDTO extends LaravelArcDTO {
    #[Property(type: 'date', required: true)]
    public Carbon $created_at;
    
    #[Property(type: 'enum', class: UserStatus::class)]
    public UserStatus $status;
    
    #[Property(type: 'nested', class: AddressDTO::class)]
    public AddressDTO $address;
}
```

### ✨ New Features

#### 🎯 Unified Property Attribute
- **Single source of truth**: One `Property` attribute for all types
- **Clean syntax**: `Property(type: 'enum|date|nested|collection', class: ClassName::class)`
- **Type safety**: Full static analysis support with PHPStan Level 6
- **Consistency**: Same pattern for all complex types

#### 🔧 Enhanced Type System
- **Explicit type declaration**: Clear intent with `type` parameter
- **Class specification**: Single `class` parameter for all reference types
- **Better IDE support**: Improved autocompletion and refactoring
- **Future-proof**: Modern PHP practices and conventions

### 🧹 Code Quality Improvements

#### ✅ PHPStan Level 6 Compliance
- **Zero errors**: Complete static analysis compliance
- **Type safety**: Full type coverage across the codebase
- **Dead code elimination**: Removed unreachable code in EnumCaster
- **Unused method removal**: Cleaned up unused private methods

#### 🔄 Simplified Codebase
- **Reduced complexity**: Single attribute approach
- **Better maintainability**: Cleaner separation of concerns
- **Consistent API**: No confusion between different attribute types
- **Developer experience**: More intuitive and predictable behavior

### 📚 Documentation Updates

#### 🎯 Migration Documentation
- **Complete migration guide**: Step-by-step conversion instructions
- **Syntax comparison**: Side-by-side old vs new examples
- **Benefits explanation**: Why the new syntax is better
- **Migration tools**: Clear path for existing projects

#### 💡 Enhanced Examples
- **All examples updated**: Using clean v2.0 syntax throughout
- **Best practices**: Modern PHP and Laravel conventions
- **Real-world scenarios**: Practical usage demonstrations

### 🧪 Testing

#### ✅ Comprehensive Test Suite
- **109 tests passing**: All functionality thoroughly tested
- **442 assertions**: Complete coverage of features
- **Enum functionality**: Full enum support verification
- **Laravel commands**: Artisan command compatibility confirmed

### 🚀 Performance

#### ⚡ Optimizations
- **Reduced reflection overhead**: Streamlined property detection
- **Cleaner type resolution**: More efficient casting pipeline
- **Memory efficiency**: Removed redundant attribute classes
- **Faster initialization**: Simplified property configuration

### 💎 Benefits of v2.0

#### 🎯 For Developers
- **Cleaner code**: Single attribute approach reduces cognitive load
- **Better tooling**: Enhanced IDE support and static analysis
- **Consistency**: Same pattern for all property types
- **Future-ready**: Built for modern PHP and Laravel versions

#### 🏗️ For Projects
- **Maintainability**: Simpler codebase with fewer concepts
- **Type safety**: Full static analysis coverage
- **Performance**: Optimized reflection and casting
- **Scalability**: Better architecture for large projects

### ⚠️ Upgrade Considerations

#### 🔄 Breaking Change Impact
- **Syntax only**: Core functionality remains identical
- **Simple migration**: Automated search-and-replace possible
- **No data loss**: Existing DTOs continue to work with new syntax
- **Gradual adoption**: Can be migrated incrementally

#### 🛠️ Migration Tools
```bash
# Simple regex replacements for most cases:
# DateProperty(.*) → Property(type: 'date'\1)
# EnumProperty(enumClass: → Property(type: 'enum', class:
# NestedProperty(dtoClass: → Property(type: 'nested', class:
```

---

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
    - `'created_at' => 'datetime'` → `#[Property(type: 'date')] public ?Carbon $created_at`
  
  - **🗃️ Database Schema Inspection** (Second Priority): Analyzes actual column types and constraints
    - `VARCHAR` → `string`, `INT` → `int`, `DECIMAL` → `float`
    - `NULL`/`NOT NULL` → Nullable/Non-nullable properties
    - Real-time database introspection for accurate types
  
  - **📄 Migration File Parsing** (Third Priority): Extracts types from Laravel migration files
    - `$table->string('name')` → `public ?string $name`
    - `$table->integer('age')->nullable()` → `public ?int $age`
    - `$table->boolean('is_active')` → `public bool $is_active`
    - `$table->timestamp('created_at')` → `#[Property(type: 'date')] public ?Carbon $created_at`
  
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
  - **Date Handling**: Uses `Property(type: 'date')` for timestamp fields with proper Carbon typing
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

    #[Property(type: 'date', required: false)]
    public ?Carbon $created_at;
}
```

## [1.1.0] - 2025-06-14

### 🆕 Major Feature: PHP Enums Support

#### Added
- **🎯 Complete PHP 8.1+ Enums Integration**: Full support for modern PHP enums with automatic casting
- **Unified `Property` attribute**: Enhanced Property attribute with intelligent enum detection and type safety
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
- **Enhanced DTOTrait**: Extended reflection system to support enum properties via unified Property attribute
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
    #[Property(type: 'enum', class: UserStatus::class, required: true)]
    public UserStatus $status;
    
    #[Property(type: 'enum', class: UserRole::class, default: UserRole::USER)]
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
- Unified `Property` attribute with intelligent type detection
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

