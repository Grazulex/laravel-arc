# Changelog

All notable changes to Laravel Arc will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] - 2025-01-05

### ✨ Enhanced ModelSchema Integration

#### 🚀 Comprehensive Field Type Support (65+ Types)

**Extended ModelSchemaFieldTypeMapper**
- **🌍 Geometric Types (8)**: `point`, `polygon`, `geometry`, `linestring`, `multipoint`, `multipolygon`, `multilinestring`, `geometrycollection`
- **📧 Enhanced String Types (13)**: `email`, `uuid`, `url`, `slug`, `phone`, `color`, `ip`, `ipv4`, `ipv6`, `mac`, `currency`, `locale`, `timezone`
- **📋 JSON/Array Types (5)**: `json`, `jsonb`, `set`, `array`, `collection`
- **🔢 Numeric Types (9)**: `float`, `double`, `decimal`, `money`, `int`, `bigint`, `smallint`, `tinyint`, `unsignedint`
- **📅 Date/Time Types (6)**: `datetime`, `timestamp`, `date`, `time`, `year`, `softdeletes`, `timestamps`
- **📝 Text Types (6)**: `text`, `longtext`, `mediumtext`, `varchar`, `char`, `string`
- **💾 Binary Types (4)**: `binary`, `blob`, `longblob`, `mediumblob`
- **⚡ Special Types (14)**: `enum`, `morphs`, `nullablemorphs`, `uuidmorphs`, etc.

**Enhanced Testing Coverage**
- `ModelSchemaFieldTypeMapperEnhancedTest`: 65+ type mappings validation (12 tests, 73 assertions)
- `ComprehensiveModelSchemaIntegrationTest`: End-to-end integration testing
- Complete type mapping coverage validation

**Enhanced Integration Statistics**
- Field types available: 65+
- Geometric types: 8
- JSON types: 5  
- Enhanced string types: 13
- Type mapping coverage: comprehensive

### Added
- **🚀 ModelSchema Integration** - Support for 65+ advanced field types through grazulex/laravel-modelschema
- **🌍 Geometric Field Types** - `point`, `polygon`, `geometry`, `linestring` for location-based applications
- **📋 Enhanced JSON Types** - `json`, `jsonb`, `set`, `array` with advanced validation
- **📧 Advanced String Types** - `email`, `uuid`, `url`, `slug`, `phone` with built-in validation
- **🔢 Numeric Variations** - `bigint`, `tinyint`, `decimal`, `money` for precise data modeling
- **MinimalModelSchemaIntegrationService** - Efficient integration avoiding recursion issues
- **ModelSchemaFieldTypeMapper** - Automatic type mapping from ModelSchema to Arc types

### Changed
- **DtoGenerateCommand** now uses ModelSchema for advanced field type processing
- Enhanced field processing pipeline with metadata preservation
- Improved type safety with ModelSchema field validation

### Technical Details
- Added `grazulex/laravel-modelschema: "^1.1.0"` dependency
- Created adapter layer for seamless ModelSchema integration
- Maintained backward compatibility with existing YAML definitions
- All existing tests pass (498/498) with new integration

### Examples

**Before (Basic Arc)**
```yaml
fields:
  location:
    type: string  # Limited to basic types
```

**After (ModelSchema Integration)**
```yaml
fields:
  coordinates:
    type: point     # ← Geographic coordinates
  boundary:
    type: polygon   # ← Geographic boundaries  
  metadata:
    type: json      # ← Structured JSON data
  tags:
    type: set       # ← Collection of unique values
```

**Generated DTO**
```php
final class LocationDTO 
{
    public function __construct(
        public readonly string $coordinates,  // point → string
        public readonly string $boundary,     // polygon → string
        public readonly array $metadata,      // json → array
        public readonly array $tags,          // set → array
    ) {}
}
```

## [Previous Versions]

For versions prior to ModelSchema integration, please refer to Git history.
