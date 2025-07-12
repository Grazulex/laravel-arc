# DtoGenerationException - Enhanced Error Handling

The `DtoGenerationException` class provides comprehensive error handling for DTO generation failures, offering detailed error messages, context information, and actionable suggestions for developers.

## Features

- **Detailed Error Messages**: Clear, specific error descriptions
- **Context Information**: File paths, DTO names, field names, and error context
- **Actionable Suggestions**: Helpful tips for resolving issues
- **CLI-Friendly Output**: Formatted error messages for terminal display
- **Error Categorization**: Different exception types for different failure scenarios

## Exception Types

### 1. YAML Parsing Errors

**Error Code**: 1001  
**Context**: YAML Parsing  
**Triggered When**: YAML file has syntax errors or invalid structure

```php
DtoGenerationException::yamlParsingError(
    '/path/to/user.yaml',
    'Syntax error on line 5',
    $originalException
);
```

**Common Causes**:
- Invalid YAML syntax
- Incorrect indentation
- Missing quotes around strings with special characters
- Invalid characters in YAML

**Suggestions**:
- Check YAML syntax and indentation
- Verify all required sections are present (header, fields)
- Ensure proper YAML formatting

### 2. Missing Required Headers

**Error Code**: 1002  
**Context**: Header Validation  
**Triggered When**: Required header fields are missing from YAML definition

```php
DtoGenerationException::missingHeader('/path/to/user.yaml', 'dto');
```

**Common Causes**:
- Missing `dto` name in header section
- Incomplete header configuration

**Suggestions**:
- Add the missing header key to the header section
- Check the YAML schema documentation
- Verify header section structure

### 3. Invalid Field Configuration

**Error Code**: 1003  
**Context**: Field Configuration  
**Triggered When**: Field definitions contain invalid attributes or values

```php
DtoGenerationException::invalidField(
    '/path/to/user.yaml',
    'email',
    'Invalid validation rule',
    'UserDTO'
);
```

**Common Causes**:
- Invalid field attributes
- Incorrect field configuration
- Field generation failures

**Suggestions**:
- Check field type and configuration
- Verify field attributes are valid
- See field types documentation

### 4. Unsupported Field Types

**Error Code**: 1004  
**Context**: Field Type  
**Triggered When**: Field uses an unsupported or invalid type

```php
DtoGenerationException::unsupportedFieldType(
    '/path/to/user.yaml',
    'custom_field',
    'unknown_type',
    'UserDTO'
);
```

**Common Causes**:
- Typo in field type name
- Using non-existent field type
- Invalid field type configuration

**Suggestions**:
- Use a supported field type (string, integer, float, boolean, array, etc.)
- Check the field types documentation
- Verify spelling of field type

### 5. Namespace Resolution Errors

**Error Code**: 1005  
**Context**: Namespace Resolution  
**Triggered When**: Namespace format is invalid or cannot be resolved

```php
DtoGenerationException::namespaceResolutionError(
    '/path/to/user.yaml',
    'Invalid\\\\Namespace',
    'Contains double backslashes',
    'UserDTO'
);
```

**Common Causes**:
- Invalid namespace format
- Double backslashes in namespace
- Non-standard namespace structure

**Suggestions**:
- Check namespace format and validity
- Verify namespace follows PHP standards
- Ensure proper directory structure

### 6. File Writing Errors

**Error Code**: 1006  
**Context**: File Writing  
**Triggered When**: Generated DTO cannot be written to filesystem

```php
DtoGenerationException::fileWriteError(
    '/path/to/user.yaml',
    '/read-only/UserDTO.php',
    'Permission denied',
    'UserDTO'
);
```

**Common Causes**:
- Insufficient file permissions
- Read-only filesystem
- Disk space issues
- Invalid output path

**Suggestions**:
- Check file permissions
- Verify directory exists and is writable
- Check available disk space

### 7. Validation Rule Errors

**Error Code**: 1007  
**Context**: Validation Rules  
**Triggered When**: Field validation rules are invalid or incompatible

```php
DtoGenerationException::validationRuleError(
    '/path/to/user.yaml',
    'email',
    'Invalid email rule syntax',
    'UserDTO'
);
```

**Common Causes**:
- Invalid Laravel validation rule syntax
- Incompatible rules with field type
- Custom rule errors

**Suggestions**:
- Check validation rule syntax
- Verify rule compatibility with field type
- See Laravel validation documentation

### 8. Circular Dependencies

**Error Code**: 1008  
**Context**: Circular Dependencies  
**Triggered When**: DTOs have circular references that would cause infinite loops

```php
DtoGenerationException::circularDependency(
    '/path/to/user.yaml',
    'UserDTO',
    ['UserDTO', 'ProfileDTO', 'UserDTO']
);
```

**Common Causes**:
- DTOs referencing each other in a loop
- Deeply nested DTO structures
- Incorrect relationship definitions

**Suggestions**:
- Remove circular references between DTOs
- Consider using array type instead of DTO type
- Restructure DTO relationships

### 9. Enum Configuration Errors

**Error Code**: 1009  
**Context**: Enum Configuration  
**Triggered When**: Enum field configuration is invalid

```php
DtoGenerationException::enumConfigurationError(
    '/path/to/user.yaml',
    'status',
    'Enum class not found',
    'UserDTO'
);
```

**Common Causes**:
- Non-existent enum class
- Invalid enum values
- Incorrect enum configuration

**Suggestions**:
- Check enum class exists and is valid
- Verify enum values are properly defined
- See enum configuration documentation

### 10. Relation Configuration Errors

**Error Code**: 1010  
**Context**: Relation Configuration  
**Triggered When**: Relationship definitions are invalid

```php
DtoGenerationException::relationConfigurationError(
    '/path/to/user.yaml',
    'posts',
    'Target model not found',
    'UserDTO'
);
```

**Common Causes**:
- Invalid relation type
- Non-existent target model
- Incorrect relation configuration

**Suggestions**:
- Check relation type and target model
- Verify target model class exists
- See relations documentation

## Usage Examples

### Basic Exception Handling

```php
use Grazulex\LaravelArc\Exceptions\DtoGenerationException;
use Grazulex\LaravelArc\Generator\DtoGenerator;

try {
    $generator = DtoGenerator::make();
    $code = $generator->generateFromDefinition($yamlDefinition);
} catch (DtoGenerationException $e) {
    // Handle with detailed error information
    echo $e->getFormattedMessage();
    
    // Access specific error details
    $yamlFile = $e->getYamlFile();
    $dtoName = $e->getDtoName();
    $fieldName = $e->getFieldName();
    $context = $e->getContext();
    $suggestions = $e->getSuggestions();
}
```

### CLI Error Display

The exception provides formatted output specifically designed for command-line interfaces:

```php
$exception = DtoGenerationException::invalidField(
    '/path/to/user.yaml',
    'email',
    'Invalid validation rule',
    'UserDTO'
);

echo $exception->getFormattedMessage();
```

**Output**:
```
❌ DTO Generation Error (Field Configuration)

Error: Invalid field 'email': Invalid validation rule
File: /path/to/user.yaml
DTO: UserDTO
Field: email

💡 Suggestions:
  • Check field type and configuration
  • Verify field attributes are valid
  • See field types documentation
```

### Adding Custom Suggestions

```php
$exception = DtoGenerationException::missingHeader('/path/to/user.yaml', 'dto');
$exception->addSuggestion('Consider using the dto:definition-init command to generate a template');

echo $exception->getFormattedMessage();
```

## Integration with CLI Commands

The `dto:generate` command automatically catches and displays `DtoGenerationException` instances with user-friendly formatting:

```bash
php artisan dto:generate invalid-user.yaml
```

**Output**:
```
❌ DTO Generation Error (Field Type)

Error: Unsupported field type 'unknown_type' for field 'custom_field'
File: /path/to/definitions/invalid-user.yaml
DTO: UserDTO
Field: custom_field

💡 Suggestions:
  • Use a supported field type (string, integer, float, boolean, array, etc.)
  • Check the field types documentation
  • Verify spelling of field type
```

## Error Prevention Best Practices

### 1. Use Schema Validation
Always validate your YAML files against the Laravel Arc schema before generation.

### 2. Test Field Types
Verify field types are supported before using them in production.

### 3. Validate Namespaces
Use `DtoPathResolver::isValidNamespace()` to validate custom namespaces.

### 4. Handle Circular References
Design DTO relationships to avoid circular dependencies.

### 5. Check File Permissions
Ensure output directories are writable before running generation.

## Debugging Tips

### Enable Verbose Mode
Use the `--verbose` flag with artisan commands to see additional error details:

```bash
php artisan dto:generate user.yaml --verbose
```

### Check Error Context
Always review the error context to understand where the failure occurred:

```php
$context = $exception->getContext();
$fieldName = $exception->getFieldName();
$suggestions = $exception->getSuggestions();
```

### Review Technical Details
When available, check the previous exception for low-level technical information:

```php
$previousException = $exception->getPrevious();
if ($previousException) {
    echo "Technical details: " . $previousException->getMessage();
}
```

## Testing Exception Handling

### Unit Tests
Test exception creation and formatting:

```php
it('creates helpful error messages', function () {
    $exception = DtoGenerationException::invalidField(
        '/path/to/user.yaml',
        'email',
        'Invalid rule',
        'UserDTO'
    );
    
    expect($exception->getFormattedMessage())->toContain('Invalid field');
    expect($exception->getSuggestions())->toContain('Check field type');
});
```

### Integration Tests
Test real-world error scenarios:

```php
it('handles invalid YAML gracefully', function () {
    $result = Artisan::call('dto:generate', ['filename' => 'invalid.yaml']);
    
    expect($result)->toBe(1);
    expect(Artisan::output())->toContain('💡 Suggestions:');
});
```

## Error Codes Reference

| Code | Type | Description |
|------|------|-------------|
| 1001 | YAML Parsing | YAML syntax or structure errors |
| 1002 | Missing Header | Required header fields missing |
| 1003 | Invalid Field | Field configuration errors |
| 1004 | Unsupported Field Type | Unknown or invalid field types |
| 1005 | Namespace Resolution | Invalid namespace format |
| 1006 | File Writing | Filesystem write errors |
| 1007 | Validation Rules | Invalid validation rule configuration |
| 1008 | Circular Dependencies | Circular DTO references |
| 1009 | Enum Configuration | Enum setup errors |
| 1010 | Relation Configuration | Relationship definition errors |
| 1020 | General Generation | Generic generation failures |

This comprehensive error handling system ensures that developers receive clear, actionable feedback when DTO generation fails, significantly improving the development experience and reducing debugging time.
