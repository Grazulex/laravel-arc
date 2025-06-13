# Changelog

All notable changes to `laravel-arc` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

