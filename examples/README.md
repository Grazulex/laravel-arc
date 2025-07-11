# Laravel Arc Examples

This directory contains example YAML definition files demonstrating various features of Laravel Arc.

## Files

### Basic Examples

#### user.yaml
A simple user DTO showing basic field types, validation rules, and header features with custom `use` statements and `extends` clause.

#### product.yaml  
A comprehensive product DTO demonstrating:
- All common field types (string, integer, decimal, boolean, enum, array, json, datetime)
- Validation rules
- Default values
- Relationships (belongsTo, hasMany)
- Options configuration

#### profile.yaml
A profile DTO designed to be used as a nested DTO in other definitions.

### Advanced Nested DTO Examples

#### nested-order.yaml
A comprehensive e-commerce order DTO demonstrating:
- Complex nested DTO relationships (customer, addresses, payment methods)
- Multiple field types and validation rules
- Relationship definitions
- Financial calculations with decimal fields
- Status management with enums

#### nested-customer.yaml
A customer DTO showing:
- Nested profile and address DTOs
- Customer preferences and settings
- Marketing opt-in management
- Customer tier/level system
- Multiple address relationships

#### nested-address.yaml
An address DTO demonstrating:
- Nested country DTO (deeper nesting level)
- Address validation and geocoding
- Multiple address types (billing, shipping)
- Delivery instructions
- Default address flags

#### nested-country.yaml
A country DTO showing:
- Deep nesting levels (4th level in chain)
- Localization data
- Geographic information
- Timezone and locale settings
- How depth limiting works in practice

#### circular-category.yaml
A category DTO demonstrating:
- Circular reference protection (parent/child categories)
- Safe handling of self-referencing DTOs
- SEO fields and metadata
- Category hierarchy management
- Path building for nested categories

## Advanced Features Demonstrated

### Nested DTO Relationships
The examples show how Laravel Arc handles:
- **Circular Reference Protection**: `circular-category.yaml` shows how parent/child relationships are safely handled
- **Depth Limiting**: The order → address → country → region chain demonstrates automatic depth limiting
- **Validation**: All nested DTOs are validated as arrays with custom rules
- **Namespace Organization**: Different namespaces for different domain areas

### Depth Limiting Example
In the e-commerce chain:
1. **OrderDTO** → **AddressDTO** (Level 1: ✓ Full nesting)
2. **AddressDTO** → **CountryDTO** (Level 2: ✓ Full nesting)
3. **CountryDTO** → **RegionDTO** (Level 3: ✓ Full nesting)
4. **RegionDTO** → **SubRegionDTO** (Level 4: ✗ Falls back to array)

### Circular Reference Example
In the category chain:
1. **CategoryDTO** → **parent** (CategoryDTO): Safe circular reference
2. **CategoryDTO** → **featured_child** (CategoryDTO): Detected and handled safely

## Usage

These examples can be used as templates for your own DTO definitions. To use them:

1. Copy the desired YAML file to your DTO definitions directory (default: `database/dto_definitions/`)
2. Modify the fields, relationships, and options as needed
3. Generate the DTO using the CLI command:

```bash
php artisan dto:generate order.yaml
```

Or programmatically:

```php
use Grazulex\LaravelArc\Generator\DtoGenerator;
use Symfony\Component\Yaml\Yaml;

$definition = Yaml::parseFile('database/dto_definitions/nested-order.yaml');
$generator = DtoGenerator::make();
$code = $generator->generateFromDefinition($definition);

file_put_contents('app/DTOs/Ecommerce/OrderDTO.php', $code);
```

## Testing Nested DTOs

All examples include proper Laravel validation rules and are designed to work with the nested DTO protection features. The examples demonstrate:

- **Safe nesting**: No infinite loops or stack overflow errors
- **Proper validation**: Each nested DTO is validated as an array
- **Performance**: Depth limiting prevents excessive nesting
- **Flexibility**: Mix of different field types and relationships

## Validation

All examples include proper Laravel validation rules. Make sure to adjust these rules according to your specific requirements and database constraints.

## Best Practices

1. **Keep nesting reasonable**: While Laravel Arc protects against infinite loops, design your DTOs to avoid overly complex nesting
2. **Use appropriate namespaces**: Organize DTOs by domain (e.g., `App\DTOs\Ecommerce`, `App\DTOs\Common`)
3. **Validate nested data**: Always include appropriate validation rules for nested DTOs
4. **Consider performance**: Deep nesting can impact performance, so use judiciously
5. **Document relationships**: Use comments to explain complex nested relationships