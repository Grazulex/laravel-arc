# 🚀 Laravel Arc v2.0.0 - Major Release

> **A Modern, Unified Approach to Laravel DTOs**

## 🎯 What's New in v2.0

Laravel Arc v2.0 introduces a **unified Property attribute** that simplifies DTO development while maintaining all the power and flexibility you love. This major release focuses on **developer experience**, **code consistency**, and **future-proofing** your Laravel DTOs.

---

## 🚨 Breaking Changes

### ⚠️ Removed Legacy Attributes

The following attribute classes have been **removed** in favor of the unified `Property` attribute:

- ❌ `DateProperty`
- ❌ `EnumProperty` 
- ❌ `NestedProperty`

### 🔄 Simple Migration

**Before (v1.x):**
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

**After (v2.0+):**
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

**Migration is simple!** Use these regex replacements:
```bash
# DateProperty(.*) → Property(type: 'date'\1)
# EnumProperty(enumClass: → Property(type: 'enum', class:
# NestedProperty(dtoClass: → Property(type: 'nested', class:
```

---

## ✨ Key Benefits

### 🎯 **Single Source of Truth**
One `Property` attribute handles all types - no more confusion between different attribute classes.

### 🔧 **Clean, Explicit Syntax**
```php
#[Property(type: 'enum', class: UserStatus::class)]
#[Property(type: 'date', format: 'Y-m-d')]
#[Property(type: 'nested', class: AddressDTO::class)]
#[Property(type: 'collection', class: ProductDTO::class)]
```

### 🧠 **Better IDE Support**
- Enhanced autocompletion
- Improved refactoring capabilities
- Better static analysis integration

### ⚡ **Performance Optimizations**
- Reduced reflection overhead
- More efficient casting pipeline
- Optimized memory usage

---

## 🔬 Code Quality Excellence

### ✅ **PHPStan Level 6 Compliance**
- **Zero errors** across the entire codebase
- Full static analysis coverage
- Enhanced type safety

### 🧪 **Comprehensive Testing**
- **109 tests passing** (442 assertions)
- Full enum functionality verified
- Laravel command compatibility confirmed

### 📏 **PSR-12 Compliant**
- Modern PHP coding standards
- Consistent code formatting
- Professional code quality

---

## 🛠️ What's Preserved

### ✅ **All Core Functionality**
- Enum support (BackedEnum & UnitEnum)
- Date handling with Carbon
- Nested DTOs and collections
- Automatic validation
- Laravel integration
- Factory pattern
- Direct property access

### ✅ **Laravel Command Still Works**
```bash
php artisan make:dto UserDTO --model=User
```
The `make:dto` command continues to work perfectly with intelligent type detection.

### ✅ **All Advanced Features**
- Custom validation rules
- Default values
- Nullable properties
- Type casting
- JSON serialization
- Array conversion

---

## 📚 Enhanced Documentation

- 📖 **Complete migration guide** with step-by-step instructions
- 💡 **Updated examples** throughout the documentation
- 🎯 **Best practices** for modern DTO development
- 🔍 **Detailed API reference** for the new syntax

---

## 🚀 Upgrade Guide

### Step 1: Update Your Dependencies
```bash
composer update grazulex/laravel-arc
```

### Step 2: Update Your DTOs
Replace legacy attributes with the new `Property` syntax:

```php
// Old
#[DateProperty(required: true, format: 'Y-m-d')]
public Carbon $date;

// New
#[Property(type: 'date', required: true, format: 'Y-m-d')]
public Carbon $date;
```

### Step 3: Verify Everything Works
```bash
php artisan test  # Run your tests
composer analyse  # Check with PHPStan
```

---

## 🎉 Why Upgrade?

### 👨‍💻 **For Developers**
- **Cleaner code** with unified syntax
- **Less cognitive load** - one attribute to remember
- **Better tooling support** for modern IDEs
- **Future-ready** architecture

### 🏢 **For Projects**
- **Improved maintainability** with simpler codebase
- **Enhanced type safety** with PHPStan Level 6
- **Better performance** with optimized internals
- **Professional code quality** with PSR-12 compliance

---

## ⚡ Quick Examples

### Basic Properties
```php
class UserDTO extends LaravelArcDTO {
    #[Property(type: 'string', required: true)]
    public string $name;
    
    #[Property(type: 'int', required: false)]
    public ?int $age;
    
    #[Property(type: 'bool', default: false)]
    public bool $is_active;
}
```

### Advanced Types
```php
class OrderDTO extends LaravelArcDTO {
    #[Property(type: 'enum', class: OrderStatus::class)]
    public OrderStatus $status;
    
    #[Property(type: 'date', format: 'Y-m-d H:i:s')]
    public Carbon $created_at;
    
    #[Property(type: 'nested', class: CustomerDTO::class)]
    public CustomerDTO $customer;
    
    #[Property(type: 'collection', class: ProductDTO::class)]
    public array $products;
}
```

---

## 🤝 Support & Migration Help

- 📚 **Full Documentation**: [GitHub Repository](https://github.com/grazulex/laravel-arc)
- 🔄 **Migration Guide**: Detailed in the [CHANGELOG.md](./CHANGELOG.md)
- 🐛 **Issues**: Report any problems on [GitHub Issues](https://github.com/grazulex/laravel-arc/issues)
- 💬 **Questions**: Use [GitHub Discussions](https://github.com/grazulex/laravel-arc/discussions)

---

## 🙏 Thank You

Thank you to all contributors and users who helped shape Laravel Arc v2.0. This release represents a significant step forward in making Laravel DTOs more powerful, consistent, and developer-friendly.

**Ready to upgrade?** The future of Laravel DTOs starts with v2.0! 🚀

---

## 📊 Full Changelog

For complete details, see the [CHANGELOG.md](./CHANGELOG.md) file.

### Quick Stats
- **Files changed**: 30+ files updated for consistency
- **Tests**: 109 tests passing (442 assertions)
- **Code quality**: PHPStan Level 6 (zero errors)
- **Performance**: Optimized reflection and casting
- **Documentation**: Completely updated with examples

