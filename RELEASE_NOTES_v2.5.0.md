# Release Notes - Version 2.5.0

## 🔄 Enhanced Date Handling & Testing

### Added
- **🎯 Rich Date Formats**: Enhanced date serialization with multiple formats
  - ISO 8601 format
  - Human-readable diff (e.g., "2 days ago")
  - UTC RFC3339
  - Formatted local time
  - Unix timestamp
  - Timezone information

### Changed
- **📅 Date Serialization**: Improved date handling in DateCaster
  - Multi-format output by default
  - Customizable format via Property attribute
  - Better timezone support
- **🧪 Updated Tests**: Modified tests to accommodate new date formats
  - Enhanced date casting tests
  - Updated integration tests

### Benefits
- **🌐 Better DateTime Support**: More flexible date handling for various use cases
- **🔄 Timezone Awareness**: Improved handling of timezone-specific data
- **📊 Rich Data Output**: More detailed date information in serialized output

## 🔄 Migration Guide

This is a minor version update that adds enhanced date handling capabilities while maintaining full backward compatibility.

### Simple Update Steps
1. Update your composer dependency: `composer update grazulex/laravel-arc`
2. No configuration changes required
3. By default, all date fields will now return a rich object with multiple formats
4. To maintain the old format, specify it explicitly: `#[Property(type: 'date', format: 'Y-m-d')]`

### Example Usage
```php
class UserDTO extends LaravelArcDTO {
    #[Property(type: 'date')]
    public Carbon $created_at;
}

$user = new UserDTO(['created_at' => '2025-06-15']);
$array = $user->toArray();

// $array['created_at'] will contain:
[
    'iso' => '2025-06-15T00:00:00+00:00',
    'diff_from_now' => '2 days ago',
    'utc' => '2025-06-15T00:00:00Z',
    'formatted' => '15/06/2025 00:00:00',
    'timestamp' => 1734480000,
    'timezone' => 'UTC',
    'local' => '15/06/2025 00:00:00'
]
```

