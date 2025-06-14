# Laravel Arc v1.2.0 - Intelligent DTO Generation 🚀

*Released: June 14, 2025*

## 🎯 Overview

Laravel Arc v1.2.0 introduces a powerful **Artisan command for intelligent DTO generation** with multi-layered type detection, making it easier than ever to create type-safe DTOs from your existing Laravel models.

---

## 🚀 Major New Features

### 🛠️ **Artisan Command: `make:dto`**

Generate DTOs automatically with intelligent type detection:

```bash
# Create a basic DTO
php artisan make:dto User

# Create from existing model with smart analysis
php artisan make:dto User --model=User

# Custom path
php artisan make:dto Product --model=Product --path=app/DTOs
```

### 🧠 **Multi-Layered Type Detection**

Intelligent type detection with prioritized sources:

1. **🎯 Model Casts Analysis** (Highest Priority)
   - Reads `$casts` property for exact type mapping
   - `'age' => 'integer'` → `public int $age`
   - `'metadata' => 'array'` → `public ?array $metadata`

2. **🗃️ Database Schema Inspection**
   - Analyzes actual column types and constraints
   - `VARCHAR` → `string`, `INT` → `int`, `DECIMAL` → `float`
   - `NULL`/`NOT NULL` → Nullable/Non-nullable

3. **📄 Migration File Parsing**
   - Extracts types from Laravel migration files
   - `$table->string('name')` → `public ?string $name`
   - `$table->integer('age')->nullable()` → `public ?int $age`

4. **🔍 Smart Pattern Matching** (Fallback)
   - `*_id` → `int`, `*_at` → `Carbon`, `email` → `string` with validation
   - `is_*`, `has_*` → `bool` with default `false`

### ⚡ **Automatic Features**

- **📋 Property Extraction**: Automatic analysis of `fillable` attributes
- **📅 Date Handling**: Uses `DateProperty` for timestamp fields
- **✅ Validation Rules**: Generates appropriate rules based on types
- **🛡️ Safety First**: Prevents overwriting existing files
- **📂 Directory Creation**: Creates target directories automatically

---

## 📊 Quality & Developer Experience Improvements

### 🎨 **Standardization**
- **File Naming**: All examples now use consistent PascalCase naming
- **Documentation**: Comprehensive updates with new command documentation
- **Examples**: Added `AdvancedModelDTO.php` and `ProductDTO.php` examples

### 🧪 **Testing Excellence**
- **86 Tests** with **327 Assertions** - comprehensive coverage
- **New Test Suites**: Complete testing for the new command functionality
- **PHPStan Level 6**: Maintained strict type safety standards
- **PSR-12 Compliance**: Code formatting standards maintained

---

## 🔧 Technical Details

### **New Components**
- `MakeDtoCommand`: Core command implementation with advanced model introspection
- `ArcServiceProvider`: Enhanced with command registration
- Comprehensive test suite: `MakeDtoCommandTest`, `MakeDtoAdvancedTest`, `MakeDtoWithModelTest`

### **Enhanced Examples**
- Renamed all example files to PascalCase for consistency
- Added `AdvancedModelDTO.php` demonstrating generated DTO with casts
- Added `ProductDTO.php` showing various property types
- Updated README with improved examples table

---

## 💡 Usage Examples

### **Basic DTO Generation**
```bash
php artisan make:dto User
```

Generates:
```php
class UserDTO extends LaravelArcDTO
{
    // Add your properties here
    // Example:
    // #[Property(type: 'string', required: true)]
    // public string $name;
}
```

### **Model-Based Generation**
```bash
php artisan make:dto User --model=User
```

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

---

## ✅ Backward Compatibility

**100% Backward Compatible** - No breaking changes
- All existing DTOs continue to work unchanged
- All existing APIs maintained
- All existing functionality preserved

---

## 🚀 Getting Started

### Installation
```bash
composer require grazulex/laravel-arc
```

### Try the New Command
```bash
# Generate a DTO from your existing User model
php artisan make:dto User --model=User

# Check the generated file
cat app/Data/UserDTO.php
```

---

## 📈 What's Next?

v1.2.0 sets the foundation for even more intelligent DTO generation. Future versions will include:
- Enhanced enum detection
- Relationship mapping
- Custom transformer plugins
- IDE integration

---

## 🙏 Credits

Special thanks to all contributors and users who provided feedback and suggestions for this release.

---

## 📞 Support

- **Documentation**: [README.md](README.md)
- **Issues**: [GitHub Issues](https://github.com/grazulex/laravel-arc/issues)
- **Discussions**: [GitHub Discussions](https://github.com/grazulex/laravel-arc/discussions)

**Happy Coding! 🎉**

