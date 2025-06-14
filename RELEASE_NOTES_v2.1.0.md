# Laravel Arc v2.1.0 Release Notes

**Release Date:** June 14, 2025

## 🎉 What's New in v2.1.0

This release introduces several powerful new features, tools, and enhancements:

### ✨ New Features & Enhancements

#### 🔄 Transformation Pipeline System
- **Pre-processing pipeline**: Apply transformations before casting
- **Built-in transformers**: TrimTransformer, LowercaseTransformer, UppercaseTransformer, HashTransformer
- **Extensible system**: Implement `TransformerInterface` for custom transformers

#### 🔍 Auto-Discovery Relations
- **Eloquent relation detection**: Automatically detect model relations
- **Smart relation mapping**: HasMany → collection, BelongsTo/HasOne → nested

#### 🛡️ Smart Validation Rules Generation
- **Intelligent rule detection**: Based on field names, types, and patterns
- **20+ built-in patterns**: Comprehensive validation rule library

#### 🔧 Debug & Analysis Tools
- **dto:analyze command**: Detailed DTO structure analysis
- **dto:validate command**: Data validation testing against DTOs

## 📚 Enhanced Documentation
- Complete documentation for all v2.0+ features
- Detailed examples and use cases

## 📖 Changes Since v2.0.0

- docs: add comprehensive GitHub release notes for v2.0.0
- feat: add comprehensive v2.0 advanced features and update documentation

## 🔄 Migration from v2.0.0

No breaking changes in this release. This update adds new features with full backward compatibility.

### Simple Update Steps:
1. Update your composer dependency: `composer update grazulex/laravel-arc`
2. Explore new features like transformation pipelines and validation tools.

## 📋 Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- Composer 2.0 or higher

## 🔗 Links

- **GitHub Repository**: [https://github.com/Grazulex/laravel-arc](https://github.com/Grazulex/laravel-arc)
- **Documentation**: Check the README.md for comprehensive guides
- **Issues**: [Report bugs or request features](https://github.com/Grazulex/laravel-arc/issues)

## 🙏 Acknowledgments

Thank you to all contributors and users who help improve Laravel Arc!

---

**Full Changelog**: [v2.0.0...v2.1.0](https://github.com/Grazulex/laravel-arc/compare/v2.0.0...v2.1.0)

