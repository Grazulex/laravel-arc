# Changelog

All notable changes to `laravel-arc` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

