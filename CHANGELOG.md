# Changelog

All notable changes to `laravel-arc` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] - 2025-06-14

### Added
- **📚 Comprehensive Code Quality Documentation**: Complete section in README covering all development tools
- **🛠️ Enhanced Developer Experience**: Detailed documentation for `composer analyse`, `format`, `format-check`, and `quality` commands
- **⚡ Quality Assurance Workflow**: Step-by-step development workflow for contributors
- **🎯 Code Standards Documentation**: PSR-12 and PHPStan Level 6 standards explained

### Enhanced
- **🔧 PHP CS Fixer Configuration**: Added PHP_CS_FIXER_IGNORE_ENV support for PHP 8.4 compatibility
- **📝 Type Annotations**: Comprehensive PHPDoc type annotations for better IDE support
- **🧪 Code Quality**: Zero PHPStan Level 6 errors across entire codebase
- **🎨 Code Formatting**: All code properly formatted according to PSR-12 standards

### Fixed
- **🐛 PHPStan Type Issues**: Resolved missing array type specifications
- **🔍 Static Analysis**: Fixed all type-related warnings in CastManager and examples
- **⚙️ PHP 8.4 Compatibility**: Updated scripts to handle PHP CS Fixer version constraints
- **📁 File Organization**: Renamed example files for better PSR compliance

### Developer Tools
- **composer analyse**: PHPStan Level 6 static analysis
- **composer format**: Automatic code formatting with PHP CS Fixer
- **composer format-check**: Format verification without modifications
- **composer quality**: Complete quality suite (format + analysis + tests)

### Documentation
- **Code Quality & Development Tools**: New comprehensive section in README
- **Development Workflow**: Best practices for contributors
- **Quality Standards**: Detailed explanation of code quality requirements
- **Tool Usage**: Complete documentation for all composer scripts

## [2.0.0] - 2025-06-14

### Added
- **🆕 Automatic Carbon Date Transformation**: New `DateProperty` attribute for automatic conversion of dates to Carbon instances
  - Support for custom date formats
  - Timezone support
  - Unix timestamp parsing
  - Both Carbon and CarbonImmutable support
  - Automatic serialization back to strings
- **🆕 Nested DTOs**: New `NestedProperty` attribute for embedding DTOs within other DTOs
  - Automatic conversion from arrays to DTO instances
  - Support for collections of nested DTOs
  - Proper serialization and deserialization
- **🆕 Advanced Casting System**: New `CastManager` class for handling complex data transformations
  - Extensible casting system
  - Error handling with detailed exceptions
  - Reverse casting for serialization
- **🆕 Enhanced Property Attributes**: Extended `Property` attribute with new parameters
  - `cast` parameter for specifying casting behavior
  - `dateFormat` for custom date formats
  - `nested` for nested DTO class specification

### Enhanced
- **Improved null handling**: Better support for nullable properties
- **Enhanced serialization**: toArray() now properly handles nested DTOs and dates
- **Better error messages**: More descriptive exceptions for casting errors
- **Type safety**: Improved type checking with casting support

### Examples
- Added `AdvancedExampleDTO.php` - Complex nested structures with dates
- Added `demo_advanced_features.php` - Practical demonstration of new features
- Added comprehensive test suite for advanced features

### Dependencies
- Added `nesbot/carbon` dependency for date handling

## [1.4.0] - 2025-06-14

### 🎉 Major Quality & CI/CD Improvements

### Added
- ✅ **Comprehensive PHPDoc type annotations** throughout the codebase
- ✅ **Enhanced type safety** with `@param` and `@return` annotations using generics
- ✅ **Complete GitHub Actions automation** for releases, tests, and code quality
- ✅ **Automated Packagist synchronization** on new releases
- ✅ **Modern release workflow** with automatic changelog generation
- ✅ **PHP CS Fixer integration** with comprehensive style rules
- ✅ **PHPStan Level 6 analysis** with zero critical errors

### Fixed
- 🐛 **PSR-4 compliance issues** in example files
- 🐛 **Pest test compatibility** with static closures (removed static keywords)
- 🐛 **GitHub Actions workflow errors** for code quality and testing
- 🐛 **Packagist version sync issues** by removing fixed version from composer.json
- 🐛 **PHP CS Fixer PHP 8.4 compatibility** with `PHP_CS_FIXER_IGNORE_ENV=1`
- 🐛 **GitHub release permissions** with proper `contents: write` and `packages: write`
- 🐛 **Deprecated PHP CS Fixer rules** (`escape_implicit_backslashes` → `string_implicit_backslashes`)

### Changed
- 🔄 **GitHub Actions modernization** - replaced deprecated `actions/create-release@v1` with `softprops/action-gh-release@v2`
- 🔄 **Optimized PHPStan configuration** with targeted error ignoring for dynamic properties
- 🔄 **PHP CS Fixer scope** - now focuses only on `src/` directory to avoid test file conflicts
- 🔄 **Improved workflow efficiency** by removing redundant dependency installations
- 🔄 **Enhanced example files** with proper namespacing (`Grazulex\Arc\Examples`)

### Technical Improvements
- 🚀 **PHPStan errors reduced** from 124 to 0 critical errors
- 🚀 **Complete type coverage** for better IDE support and maintainability
- 🚀 **Robust CI/CD pipeline** with automated quality checks
- 🚀 **Production-ready release automation** with comprehensive testing
- 🚀 **Better developer experience** with clear type hints and documentation

### Developer Experience
- 🛠️ **Improved IDE integration** with comprehensive type annotations
- 🛠️ **Better error messages** and debugging capabilities
- 🛠️ **Automated code formatting** and style enforcement
- 🛠️ **Reliable continuous integration** for all contributions

## [1.1.0] - 2025-06-13

### 🎯 Breaking Changes
- ❌ **Dropped support for Laravel 10** - Now requires Laravel 11+ only
- ⚠️ This is a **MINOR** version due to improved compatibility strategy

### Added
- ✅ **Focused Laravel 11+ support** for better compatibility
- ✅ **Upgraded to Laravel 12** with latest features
- ✅ **Enhanced Pest 3.x support** with latest testing features
- ✅ **PHPUnit 11.x compatibility** for modern testing
- ✅ **Simplified dependency matrix** for CI/CD

### Changed
- 🔄 **Modernized technology stack** - Laravel 11+/12+ only
- 🔄 **Updated GitHub Actions** to focus on Laravel 11+ and 12+
- 🔄 **Simplified composer requirements** for better stability
- 🔄 **Enhanced testing matrix** with PHP 8.2, 8.3, 8.4

### Improved
- 🚀 **Better CI/CD performance** with focused version matrix
- 🚀 **Reduced dependency conflicts** by targeting modern Laravel
- 🚀 **Future-proof architecture** aligned with Laravel's roadmap

## [1.0.1] - 2025-06-13

### Added
- ✅ Support for Laravel 12
- ✅ Support for PHP 8.4
- ✅ Updated GitHub Actions workflow for modern PHP/Laravel versions

### Changed
- 🔄 Minimum PHP version now 8.2+ (was 8.1+)
- 🔄 Updated composer dependencies to support Laravel 10+/11+/12+
- 🔄 Updated GitHub Actions to test with PHP 8.2, 8.3, 8.4
- 🔄 Updated Testbench dependencies for Laravel 12 compatibility

### Fixed
- 🐛 GitHub Actions workflow failures with newer Laravel/PHP versions

## [1.0.0] - 2025-06-13

### Added
- 🎉 Initial release of Laravel Arc
- ✅ Direct property access for DTOs (`$user->name`)
- ✅ Automatic validation based on PHP 8+ attributes
- ✅ Automatic Laravel validation rules generation
- ✅ Real-time type validation
- ✅ Default values support
- ✅ Detailed exceptions with `InvalidDTOException`
- ✅ Simple and intuitive API
- ✅ Property attribute with type, required, default, and validation options
- ✅ Comprehensive test suite using Pest
- ✅ Complete English documentation
- ✅ Support for PHP 8.1+ and Laravel 10+
- ✅ Examples in `src/Examples/` folder
- ✅ Magic methods for getter/setter compatibility
- ✅ Array and JSON conversion methods

### Features
- `LaravelArcDTO` base class for creating DTOs
- `Property` attribute for defining property characteristics
- `ArcServiceProvider` for Laravel integration
- `DTOInterface` and `DTOTrait` for extensibility
- Unit and Feature tests with 42 passing tests
- Real-world usage examples

[1.0.0]: https://github.com/grazulex/laravel-arc/releases/tag/v1.0.0

