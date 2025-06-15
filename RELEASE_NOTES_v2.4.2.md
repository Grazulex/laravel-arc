# Laravel Arc v2.4.2

## 🛠️ PHP 8.4 Compatibility Update

**Release Date:** June 15, 2025  
**Type:** Patch Version - Compatibility Update  
**Compatibility:** Laravel 11+ | 12+, PHP 8.3+

This release adds support for PHP 8.4 while maintaining the package's core functionality and modernizing the codebase.

---

## 🔄 Key Changes

### Enhanced PHP Compatibility
- **✨ PHP 8.4 Support**: Full compatibility with PHP 8.4
- **🔄 Modernized Attribute Usage**: Updated to unified Property attribute system
- **🛡️ Code Quality**: Improved static analysis compatibility

### Unified Property System
- **🎯 Simplified Attributes**: Consolidated attribute usage under unified Property system
- **📅 Date Handling**: Using `Property(type: 'date')` instead of legacy `DateProperty`
- **🧹 Code Cleanup**: Removed deprecated attribute references

### Developer Experience
- **🚀 Better IDE Support**: Enhanced type hints and attribute suggestions
- **📚 Updated Documentation**: Clear examples with modern attribute syntax
- **🧪 Improved Tests**: Updated test suite for PHP 8.4 compatibility

---

## 🔍 Technical Details

### Updated File Examples

**Before (Legacy):**
```php
class UserDTO extends LaravelArcDTO
{
    #[DateProperty(required: true)]
    public Carbon $created_at;
}
```

**After (Modern):**
```php
class UserDTO extends LaravelArcDTO
{
    #[Property(type: 'date', required: true)]
    public Carbon $created_at;
}
```

### Test Suite Updates
- ✅ All tests passing (152 tests, 551 assertions)
- ✅ Updated attribute expectations in tests
- ✅ Enhanced PHP 8.4 compatibility checks

---

## 🚀 Migration from v2.4.1

**This is a seamless update with no breaking changes.**

### Simple Update Steps

1. **Update your dependency:**
   ```bash
   composer update grazulex/laravel-arc
   ```

2. **No configuration changes required** - Everything works the same way

3. **Optional: Update Your DTOs**
   - While not required, you can update your DTOs to use the modern syntax
   - Replace any remaining uses of `DateProperty` with `Property(type: 'date')`

### What You Get

- ✅ **PHP 8.4 Support**: Full compatibility with latest PHP
- ✅ **Modern Syntax**: Cleaner, more consistent attribute usage
- ✅ **Same Features**: All existing functionality preserved
- ✅ **Better IDE Support**: Enhanced type hints and completions

---

## 📊 Quality Metrics

- ✅ **PHPStan Level 6**: Zero static analysis errors
- ✅ **152 Tests Passing**: Complete test suite validation
- ✅ **551 Assertions**: Comprehensive feature coverage
- ✅ **PSR-12 compliant**: Clean, standardized code
- ✅ **Backward compatible**: No breaking changes

---

## 🆘 Support

If you encounter any issues with this release:

1. **Check the documentation**: [Laravel Arc Wiki](https://github.com/Grazulex/laravel-arc/wiki)
2. **Report issues**: [GitHub Issues](https://github.com/Grazulex/laravel-arc/issues)
3. **Community support**: [GitHub Discussions](https://github.com/Grazulex/laravel-arc/discussions)

---

## 🎉 Thank You

Thank you to everyone who helped test and verify PHP 8.4 compatibility!

**Happy coding! 🚀**

---

*Laravel Arc v2.4.2 - Modern,

