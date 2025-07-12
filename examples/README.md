# Laravel Arc Examples

<div align="center">
  <p><strong>Comprehensive examples demonstrating Laravel Arc's capabilities</strong></p>
</div>

This directory contains carefully crafted example YAML definition files showcasing various features and patterns of Laravel Arc. These examples serve as both learning resources and templates for your own DTO definitions.

## 📁 Example Categories

### 🎯 Basic Examples
Perfect for getting started with Laravel Arc.

**💡 Want to learn how to use these DTOs in your application?** 
Check out our [**DTO Usage Guide**](../docs/DTO_USAGE_GUIDE.md) for comprehensive examples of using DTOs in controllers, services, validation, and more!

#### [`user.yaml`](user.yaml)
A simple user DTO demonstrating:
- Basic field types and validation
- Header features with custom `use` statements
- Extends clause for base classes
- Timestamp and soft delete options

#### [`product.yaml`](product.yaml)
A comprehensive product DTO showcasing:
- All common field types (string, integer, decimal, boolean, enum, array, json, datetime)
- Comprehensive validation rules
- Default values and required fields
- Eloquent relationships (belongsTo, hasMany)
- Professional namespace organization

#### [`profile.yaml`](profile.yaml)
A profile DTO designed for nested relationships:
- Personal information fields
- Validation for user data
- Optimized for use as nested DTO
- Clean field structure

### 🔢 Enum Examples
Demonstrating Laravel Arc's powerful enum support.

#### [`enum-examples.yaml`](enum-examples.yaml)
A comprehensive enum demonstration featuring:
- **Traditional enums** with values array
- **PHP string enum classes** with type safety
- **PHP int enum classes** for numeric values
- **Default values** with enum classes
- **Explicit enum case references** 
- **Optional enum fields** with nullable support

#### [`php-enum-classes.php`](php-enum-classes.php)
Production-ready PHP enum class examples:
- String enums (Priority, Category, Visibility)
- Int enums (Level, Rating)
- Pure enums without backing types (Color)
- E-commerce enums (OrderStatus, Currency)
- Proper enum class structure for Laravel Arc

### 🏗️ Advanced Nested DTO Examples
Showcasing complex nested relationships and advanced features.

#### [`nested-order.yaml`](nested-order.yaml)
A comprehensive e-commerce order DTO demonstrating:
- **Complex nested relationships** (customer, addresses, payment methods)
- **Multiple field types** with comprehensive validation
- **Financial calculations** with decimal precision
- **Status management** using PHP enum classes
- **Relationship definitions** for order management
- **Professional namespace organization**

#### [`nested-customer.yaml`](nested-customer.yaml)
A customer DTO showcasing:
- **Nested profile and address DTOs** for complex data structures
- **Customer preferences** and settings management
- **Marketing opt-in** management with boolean fields
- **Customer tier/level** system using enums
- **Multiple address relationships** (billing, shipping, delivery)

#### [`nested-address.yaml`](nested-address.yaml)
An address DTO demonstrating:
- **Nested country DTO** (deeper nesting levels)
- **Address validation** with comprehensive rules
- **Geocoding support** with latitude/longitude
- **Address types** (billing, shipping, delivery)
- **Delivery instructions** with text fields
- **Default address flags** for user preferences

#### [`nested-country.yaml`](nested-country.yaml)
A country DTO showing:
- **Deep nesting levels** (4th level in chain)
- **Localization data** with multiple languages
- **Geographic information** (timezone, currency, calling code)
- **Regional settings** and locale configuration
- **Depth limiting** demonstration in practice

#### [`circular-category.yaml`](circular-category.yaml)
A category DTO demonstrating:
- **Circular reference protection** (parent/child categories)
- **Safe self-referencing** DTO handling
- **SEO fields** and metadata management
- **Category hierarchy** with parent/child relationships
- **Path building** for nested category structures

## 🚀 Getting Started with Examples

### Quick Start
1. **Copy an example** to your DTO definitions directory:
   ```bash
   cp examples/user.yaml database/dto_definitions/
   ```

2. **Generate the DTO**:
   ```bash
   php artisan dto:generate user.yaml
   ```

3. **Use in your application**:
   ```php
   use App\DTOs\UserDTO;
   
   $user = new UserDTO();
   $user->name = 'John Doe';
   $user->email = 'john@example.com';
   ```

### Programmatic Usage
```php
use Grazulex\LaravelArc\Generator\DtoGenerator;
use Symfony\Component\Yaml\Yaml;

$definition = Yaml::parseFile('examples/nested-order.yaml');
$generator = DtoGenerator::make();
$code = $generator->generateFromDefinition($definition);

file_put_contents('app/DTOs/Ecommerce/OrderDTO.php', $code);
```

## 🎓 Learning Path

### 1. Start with Basic Examples
- [`user.yaml`](user.yaml) - Learn basic field types and validation
- [`product.yaml`](product.yaml) - Understand relationships and options
- [`profile.yaml`](profile.yaml) - See nested DTO preparation

### 2. Explore Enum Features
- [`enum-examples.yaml`](enum-examples.yaml) - Master enum definitions
- [`php-enum-classes.php`](php-enum-classes.php) - Learn PHP enum classes

### 3. Master Advanced Features
- [`nested-order.yaml`](nested-order.yaml) - Complex e-commerce patterns
- [`nested-customer.yaml`](nested-customer.yaml) - Multi-level nesting
- [`circular-category.yaml`](circular-category.yaml) - Circular reference handling

## 🔧 Advanced Features Demonstrated

### Enum Support
Laravel Arc provides comprehensive enum support with these examples:

#### Traditional Enums
```yaml
status:
  type: enum
  values: [draft, published, archived]
  default: draft
```

#### PHP Enum Classes (Recommended)
```yaml
status:
  type: enum
  class: App\Enums\Status
  default: draft
```

**Benefits:**
- ✅ Type safety and IDE support
- ✅ Automatic validation with Laravel's `enum:` rule
- ✅ Better code organization
- ✅ Support for both backed and unbacked enums

### Nested DTO Relationships
The examples showcase Laravel Arc's nested DTO capabilities:

#### Circular Reference Protection
```yaml
# In circular-category.yaml
parent:
  type: dto
  dto: CategoryDTO  # Self-reference handled safely
  required: false
```

#### Depth Limiting
The nested chain demonstrates automatic depth limiting:
1. **OrderDTO** → **AddressDTO** (Level 1: ✓ Full nesting)
2. **AddressDTO** → **CountryDTO** (Level 2: ✓ Full nesting)
3. **CountryDTO** → **RegionDTO** (Level 3: ✓ Full nesting)
4. **RegionDTO** → **SubRegionDTO** (Level 4: ✗ Falls back to array)

### Professional Namespace Organization
```yaml
options:
  namespace: App\DTOs\Ecommerce  # Domain-specific organization
  timestamps: true
  soft_deletes: true
```

## 📝 Best Practices Demonstrated

### 1. Field Design
- **Use appropriate types** for data (decimal for money, uuid for IDs)
- **Set sensible defaults** for optional fields
- **Include comprehensive validation** rules
- **Use descriptive field names** that match your domain

### 2. Enum Usage
- **Prefer PHP enum classes** over traditional enums
- **Use consistent naming** (UPPER_CASE for cases)
- **Provide default values** for better UX
- **Leverage automatic validation** with enum classes

### 3. Nested Relationships
- **Keep nesting reasonable** (3-4 levels maximum)
- **Use appropriate namespaces** for organization
- **Validate nested data** with array rules
- **Consider performance** implications

### 4. Validation Strategy
- **Be specific** with validation rules
- **Use Laravel's built-in rules** when possible
- **Combine rules** for comprehensive validation
- **Test edge cases** with your validation

## 🧪 Testing with Examples

All examples include proper Laravel validation rules and are designed to work with Laravel Arc's protection features:

```bash
# Test all examples
php artisan dto:generate --all --dry-run

# Test specific complex example
php artisan dto:generate nested-order.yaml --dry-run

# Generate with force for development
php artisan dto:generate user.yaml --force
```

### Validation Testing
```php
// Test enum validation
$order = new OrderDTO();
$order->status = OrderStatus::PENDING;  // ✅ Valid
$order->status = 'invalid';            // ❌ Validation error

// Test nested DTO validation
$order->customer = [
    'name' => 'John Doe',
    'email' => 'john@example.com'
];  // ✅ Valid array structure
```

## 🔄 Customization Examples

### Modify for Your Domain
```yaml
# Customize the user example for your needs
header:
  dto: CustomerDTO  # Change class name
  model: App\Models\Customer  # Change model
  extends: BaseCustomerDTO  # Add base class

fields:
  # Add domain-specific fields
  membership_level:
    type: enum
    class: App\Enums\MembershipLevel
    default: bronze
  
  # Customize validation
  phone:
    type: string
    rules: [phone:US, nullable]  # US phone format
```

### Environment-Specific Configuration
```yaml
options:
  timestamps: true
  soft_deletes: ${APP_ENV === 'production'}
  namespace: ${APP_ENV === 'testing' ? 'Tests\\DTOs' : 'App\\DTOs'}
```

## 📚 Additional Resources

- 🎯 [**DTO Usage Guide**](../docs/DTO_USAGE_GUIDE.md) - **How to use DTOs in your Laravel application**
- 📖 [Getting Started Guide](../docs/GETTING_STARTED.md)
- 🏷️ [Field Types Reference](../docs/FIELD_TYPES.md)
- 🔗 [Relationships Guide](../docs/RELATIONSHIPS.md)
- 🖥️ [CLI Commands](../docs/CLI_COMMANDS.md)
- 🚀 [Advanced Usage](../docs/ADVANCED_USAGE.md)

---

<div align="center">
  <p><strong>Ready to create your own DTOs?</strong></p>
  <p>Start with our <a href="../docs/GETTING_STARTED.md">Getting Started Guide</a> or explore the <a href="../docs/CLI_COMMANDS.md">CLI Commands</a></p>
</div>