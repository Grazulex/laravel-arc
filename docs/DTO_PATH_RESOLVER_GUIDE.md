# DtoPathResolver - Usage Guide

The `DtoPathResolver` is a utility class that centralizes path and namespace resolution logic for DTO generation. This class provides bidirectional methods for converting between namespaces and file paths.

## Key Features

### 1. Output Path Resolution

```php
use Grazulex\LaravelArc\Support\DtoPathResolver;

// Resolve output path based on namespace
$path = DtoPathResolver::resolveOutputPath('UserDTO', 'App\DTO\Admin');
// Result: /path/to/app/DTO/Admin/UserDTO.php
```

### 2. Namespace Derivation from Path

```php
// Derive namespace from file path
$namespace = DtoPathResolver::resolveNamespaceFromPath('/path/to/app/DTO/Admin/UserDTO.php');
// Result: App\DTO\Admin
```

### 3. Namespace Validation

```php
// Validate a namespace
$isValid = DtoPathResolver::isValidNamespace('App\DTO\Admin');
// Result: true

$isValid = DtoPathResolver::isValidNamespace('App\\DTOs'); // Double backslash
// Result: false
```

### 4. Namespace Normalization

```php
// Normalize a namespace
$normalized = DtoPathResolver::normalizeNamespace('\App\DTO\Admin\\');
// Result: App\DTO\Admin
```

### 5. Sub-namespace Relationships

```php
// Check if a namespace is a sub-namespace of another
$isSubNamespace = DtoPathResolver::isSubNamespaceOf('App\DTO\Admin', 'App\DTO');
// Result: true

$isSubNamespace = DtoPathResolver::isSubNamespaceOf('App\DTO', 'App\DTO');
// Result: false (same namespace)
```

## Usage Examples

### Standard Configuration

With standard configuration (`dto.output_path` = `app/DTO` and `dto.namespace` = `App\DTO`):

```php
// Base namespace
DtoPathResolver::resolveOutputPath('UserDTO', 'App\DTO');
// → /path/to/app/DTO/UserDTO.php

// Sub-namespace
DtoPathResolver::resolveOutputPath('AdminUserDTO', 'App\DTO\Admin');
// → /path/to/app/DTO/Admin/AdminUserDTO.php

// Deep sub-namespace
DtoPathResolver::resolveOutputPath('ProductDTO', 'App\DTO\Admin\Catalog\Products');
// → /path/to/app/DTO/Admin/Catalog/Products/ProductDTO.php
```

### External Namespace

For namespaces completely different from the base configuration:

```php
DtoPathResolver::resolveOutputPath('ExternalDTO', 'Library\External\Data');
// → /path/to/Library/External/Data/ExternalDTO.php
```

### Bidirectional Conversion

```php
$originalNamespace = 'App\DTO\Admin\Users';
$dtoName = 'UserDTO';

// 1. Resolve the path
$path = DtoPathResolver::resolveOutputPath($dtoName, $originalNamespace);

// 2. Derive namespace from path
$derivedNamespace = DtoPathResolver::resolveNamespaceFromPath($path);

// 3. Verify consistency
assert($derivedNamespace === $originalNamespace); // true
```

## Integration with Artisan Command

The class is automatically used by the `dto:generate` command:

```yaml
# admin-user.yaml
header:
  dto: AdminUserDTO
  
options:
  namespace: App\DTO\Admin
  
fields:
  id:
    type: integer
    required: true
  name:
    type: string
    required: true
```

```bash
php artisan dto:generate admin-user.yaml
```

The DTO will be generated in `app/DTO/Admin/AdminUserDTO.php` with namespace `App\DTO\Admin`.

## Special Case Handling

### Windows Paths

The class automatically handles Windows paths:

```php
$windowsPath = 'C:\path\to\app\DTO\Admin\UserDTO.php';
$namespace = DtoPathResolver::resolveNamespaceFromPath($windowsPath);
// Result: App\DTO\Admin (identical to Unix paths)
```

### Acronyms and Abbreviations

The class preserves known acronyms:

```php
$namespace = DtoPathResolver::resolveNamespaceFromPath('/path/to/app/DTO/API/UserDTO.php');
// Result: App\DTO\APIs (not App\DTO\Apis)
```

## Validation and Errors

### Valid Namespaces

- `App\DTO`
- `App\DTO\Admin`
- `MyCompany\Project\DTO`
- `_Underscore\Name`

### Invalid Namespaces

- `""` (empty)
- `App\\DTO` (consecutive backslashes)
- `App\DTO\` (trailing backslash)
- `\App\DTO` (leading backslash)
- `App\123Invalid` (starts with number)
- `App\Invalid-Name` (invalid character)

## Best Practices

1. **Always use validation**: Validate your namespaces before using them
2. **Normalize inputs**: Use `normalizeNamespace()` to clean user inputs
3. **Test bidirectionality**: Verify that your conversions are consistent
4. **Follow conventions**: Adhere to PHP naming conventions for namespaces

## Testing

The class is fully tested with unit and integration tests:

```bash
# Unit tests
php vendor/bin/pest tests/Unit/Support/DtoPathResolverTest.php

# Integration tests
php vendor/bin/pest tests/Feature/DtoPathResolverIntegrationTest.php
```
