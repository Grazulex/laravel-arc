# Laravel Arc Examples

This directory contains example YAML definition files demonstrating various features of Laravel Arc.

## Files

### user.yaml
A simple user DTO showing basic field types and validation rules.

### product.yaml  
A comprehensive product DTO demonstrating:
- All common field types (string, integer, decimal, boolean, enum, array, json, datetime)
- Validation rules
- Default values
- Relationships (belongsTo, hasMany)
- Options configuration

### profile.yaml
A profile DTO designed to be used as a nested DTO in other definitions.

## Usage

These examples can be used as templates for your own DTO definitions. To use them:

1. Copy the desired YAML file to your DTO definitions directory (default: `database/dto_definitions/`)
2. Modify the fields, relationships, and options as needed
3. Generate the DTO programmatically:

```php
use Grazulex\LaravelArc\Generator\DtoGenerator;
use Symfony\Component\Yaml\Yaml;

$definition = Yaml::parseFile('database/dto_definitions/user.yaml');
$generator = DtoGenerator::make();
$code = $generator->generateFromDefinition($definition);

file_put_contents('app/DTOs/UserDTO.php', $code);
```

## Validation

All examples include proper Laravel validation rules. Make sure to adjust these rules according to your specific requirements and database constraints.