# Release Notes v2.3.0

## 🔄 Refactoring & Documentation

### 🏷️ Trait Renaming
- **Renamed trait**: `FromModelTrait` is now `DTOFromModelTrait` for better clarity and consistency
- **Fixed references**: Updated all test files and documentation with new trait name
- **No breaking changes**: Trait functionality remains identical

### 📚 Documentation Improvements
- **Wiki integration**: Moved trait documentation to wiki for better organization
- **Enhanced clarity**: Better explanation of DTO philosophy and usage patterns
- **Added examples**: More comprehensive code examples in documentation

## 🔄 Migration from v2.2.5

No breaking changes in this release. This is a minor version update that improves naming consistency and documentation.

**Simple Update Steps:**
1. Update your composer dependency: `composer update grazulex/laravel-arc`
2. If using the trait directly, update the import statement:
   ```php
   // Before
   use Grazulex\Arc\Traits\FromModelTrait;
   // After
   use Grazulex\Arc\Traits\DTOFromModelTrait;
   ```
3. No other changes required

