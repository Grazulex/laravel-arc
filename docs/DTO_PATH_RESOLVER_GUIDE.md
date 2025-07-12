# DtoPathResolver - Usage Guide

The `DtoPathResolver` is a utility class that centralizes path and namespace resolution logic for DTO generation. This class provides bidirectional methods for converting between namespaces and file paths.

## Key Features

### 1. Output Path Resolution

```php
use Grazulex\LaravelArc\Support\DtoPathResolver;

// Resolve output path based on namespace
$path = DtoPathResolver::resolveOutputPath('UserDTO', 'App\DTOs\Admin');
// Result: /path/to/app/DTOs/Admin/UserDTO.php
```

### 2. Namespace Derivation from Path

```php
// Derive namespace from file path
$namespace = DtoPathResolver::resolveNamespaceFromPath('/path/to/app/DTOs/Admin/UserDTO.php');
// Result: App\DTOs\Admin
```

### 3. Namespace Validation

```php
// Validate a namespace
$isValid = DtoPathResolver::isValidNamespace('App\DTOs\Admin');
// Result: true

$isValid = DtoPathResolver::isValidNamespace('App\\DTOs'); // Double backslash
// Result: false
```

### 4. Namespace Normalization

```php
// Normalize a namespace
$normalized = DtoPathResolver::normalizeNamespace('\App\DTOs\Admin\\');
// Result: App\DTOs\Admin
```

### 5. Sub-namespace Relationships

```php
// Check if a namespace is a sub-namespace of another
$isSubNamespace = DtoPathResolver::isSubNamespaceOf('App\DTOs\Admin', 'App\DTOs');
// Result: true

$isSubNamespace = DtoPathResolver::isSubNamespaceOf('App\DTOs', 'App\DTOs');
// Result: false (same namespace)
```

## Usage Examples

### Standard Configuration

With standard configuration (`dto.output_path` = `app/DTOs` and `dto.namespace` = `App\DTOs`):

```php
// Base namespace
DtoPathResolver::resolveOutputPath('UserDTO', 'App\DTOs');
// → /path/to/app/DTOs/UserDTO.php

// Sub-namespace
DtoPathResolver::resolveOutputPath('AdminUserDTO', 'App\DTOs\Admin');
// → /path/to/app/DTOs/Admin/AdminUserDTO.php

// Deep sub-namespace
DtoPathResolver::resolveOutputPath('ProductDTO', 'App\DTOs\Admin\Catalog\Products');
// → /path/to/app/DTOs/Admin/Catalog/Products/ProductDTO.php
```

### External Namespace

For namespaces completely different from the base configuration:

```php
DtoPathResolver::resolveOutputPath('ExternalDTO', 'Library\External\Data');
// → /path/to/Library/External/Data/ExternalDTO.php
```

### Bidirectional Conversion

```php
$originalNamespace = 'App\DTOs\Admin\Users';
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
  namespace: App\DTOs\Admin
  
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

The DTO will be generated in `app/DTOs/Admin/AdminUserDTO.php` with namespace `App\DTOs\Admin`.

## Special Case Handling

### Windows Paths

The class automatically handles Windows paths:

```php
$windowsPath = 'C:\path\to\app\DTOs\Admin\UserDTO.php';
$namespace = DtoPathResolver::resolveNamespaceFromPath($windowsPath);
// Result: App\DTOs\Admin (identical to Unix paths)
```

### Acronyms and Abbreviations

The class preserves known acronyms:

```php
$namespace = DtoPathResolver::resolveNamespaceFromPath('/path/to/app/DTOs/APIs/UserDTO.php');
// Result: App\DTOs\APIs (not App\DTOs\Apis)
```

## Validation and Errors

### Valid Namespaces

- `App\DTOs`
- `App\DTOs\Admin`
- `MyCompany\Project\DTOs`
- `_Underscore\Name`

### Invalid Namespaces

- `""` (empty)
- `App\\DTOs` (consecutive backslashes)
- `App\DTOs\` (trailing backslash)
- `\App\DTOs` (leading backslash)
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
