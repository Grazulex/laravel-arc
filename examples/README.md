# Laravel Arc Examples

<div align="center">
  <p><strong>Comprehensive examples demonstrating Laravel Arc's capabilities</strong></p>
</div>

This directory contains carefully crafted example YAML definition files showcasing various features and patterns of Laravel Arc. These examples serve as both learning resources and templates for your own DTO definitions.

## 📁 Example Categories

### 🎯 Basic Examples
Perfect for getting started with Laravel Arc's new trait-based system.

**💡 Want to learn how to use these DTOs in your application?** 
Check out our [**DTO Usage Guide**](../docs/DTO_USAGE_GUIDE.md) for comprehensive examples of using DTOs in controllers, services, validation, and more!

#### [`user.yaml`](user.yaml)
A simple user DTO demonstrating:
- Basic field types and validation
- Modern trait-based system (HasUuid, HasTimestamps)
- Header features with custom `use` statements
- Extends clause for base classes
- Eloquent relationships

#### [`field-transformers-example.yaml`](field-transformers-example.yaml)
Comprehensive field transformers demonstration:
- All 10 available transformers (trim, lowercase, uppercase, title_case, slugify, abs, encrypt, normalize_phone, clamp_max, clamp_min)
- String transformers with practical examples
- Numeric transformers with range clamping
- Chained transformers for complex data processing
- Real-world usage patterns and comments

#### [`export-formats-comprehensive.yaml`](export-formats-comprehensive.yaml)
Complete export formats demonstration:
- All 9 single DTO export methods
- All 6 collection export methods
- Controller usage examples
- Extension requirements and recommendations
- Performance considerations

#### [`modern-traits-comprehensive.yaml`](modern-traits-comprehensive.yaml)
Modern trait system demonstration:
- All 7 behavioral traits with auto-generated fields
- All 3 functional traits (automatic in every DTO)
- Migration guide from old options system
- Detailed method documentation
- Practical usage examples

#### [`user-with-all-traits.yaml`](user-with-all-traits.yaml)
A comprehensive example showcasing all behavioral traits:
- All 7 behavioral traits (HasTimestamps, HasUuid, HasSoftDeletes, HasVersioning, HasTagging, HasAuditing, HasCaching)
- Complete field configuration with validation and transformers
- Complex relationships and enum support
- Documentation of all auto-generated fields and methods

#### [`behavioral-traits-examples.yaml`](behavioral-traits-examples.yaml)
Individual demonstrations of each behavioral trait:
- Each trait shown separately with its fields and methods
- Common trait combinations
- Migration examples from old options system
- Detailed documentation of trait capabilities

#### [`advanced-user.yaml`](advanced-user.yaml)
An advanced user DTO showcasing:
- Complex header configuration with multiple traits
- Advanced field types (enum, json, datetime)
- Professional validation rules
- Multiple relationship types
- Complete soft delete and timestamp configuration

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

### 🚀 Behavioral Traits System
Laravel Arc now uses a powerful trait-based system for adding behavioral functionality to DTOs.

#### Available Behavioral Traits (7 traits)
Instead of using the old `options` system, Laravel Arc now provides behavioral traits that can be mixed and matched:

| Trait | Description | Auto-Generated Fields | Auto-Generated Methods |
|-------|-------------|----------------------|----------------------|
| `HasTimestamps` | Timestamp management | `created_at`, `updated_at` | `touch()`, `wasRecentlyCreated()`, `getAge()` |
| `HasUuid` | UUID generation | `id` (UUID type) | UUID validation and generation |
| `HasSoftDeletes` | Soft deletion | `deleted_at` | Soft delete methods |
| `HasVersioning` | Version control | `version` | `nextVersion()`, `isNewerThan()`, `getVersionInfo()` |
| `HasTagging` | Tagging system | `tags` | `addTag()`, `removeTag()`, `hasTag()`, `getTags()` |
| `HasAuditing` | Audit trails | `created_by`, `updated_by` | `createAuditTrail()`, `setCreator()`, `setUpdater()` |
| `HasCaching` | Caching support | Cache metadata | `cache()`, `clearCache()`, `getCacheKey()`, `isCached()` |

#### Migration from Options System
**Old format (deprecated):**
```yaml
options:
  timestamps: true
  soft_deletes: true
  uuid: true
  versioning: true
  taggable: true
  auditable: true
  cacheable: true
```

**New format (recommended):**
```yaml
header:
  traits:
    - HasTimestamps
    - HasSoftDeletes
    - HasUuid
    - HasVersioning
    - HasTagging
    - HasAuditing
    - HasCaching
```

#### [`behavioral-traits-examples.yaml`](behavioral-traits-examples.yaml)
Individual demonstrations of each behavioral trait:
- Each trait shown separately with its fields and methods
- Common trait combinations
- Migration examples from old options system
- Detailed documentation of trait capabilities

#### [`advanced-options.yaml`](advanced-options.yaml)
A comprehensive demonstration of all new advanced options:
- **UUID generation** with automatic field creation and helper methods
- **Versioning system** with version tracking and comparison
- **Taggable functionality** with tag management methods
- **Immutable patterns** with `with()`, `copy()`, `equals()`, `hash()`
- **Audit trail** with creator/updater tracking
- **Caching capabilities** with cache key generation and management
- **Slug generation** from specified source fields
- **Combined usage** showing all options working together

#### [`advanced-options-usage.php`](advanced-options-usage.php)
Complete PHP examples demonstrating:
- **UUID methods**: `generateUuid()`, `withGeneratedUuid()`
- **Versioning methods**: `nextVersion()`, `isNewerThan()`, `getVersionInfo()`
- **Tag management**: `addTag()`, `removeTag()`, `hasTag()`, `getTags()`, `withTag()`
- **Immutable operations**: `with()`, `copy()`, `equals()`, `hash()`
- **Audit trail**: `createAuditTrail()`, `setCreator()`, `setUpdater()`, `getAuditInfo()`
- **Caching**: `cache()`, `fromCache()`, `clearCache()`, `isCached()`, `getCacheMetadata()`
- **Slug generation**: `generateSlug()`, `updateSlug()`, `getSlug()`, `hasUniqueSlug()`
- **Combined workflows** using multiple options together
- **Export examples** with new HTML format

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

## 🎯 Built-in Traits

Every generated DTO automatically includes three powerful traits:

### 🔍 **ValidatesData** - Validation Methods
```php
// Quick validation checks
if (UserDTO::passes($data)) {
    $validated = UserDTO::validate($data);
}

// Detailed validation
$validator = UserDTO::validator($data);
if (UserDTO::fails($data)) {
    $errors = $validator->errors();
}
```

### 🔄 **ConvertsData** - Data Conversion
```php
// Convert multiple models - Two ways:
$userDtos = UserDTO::collection($users);  // Intuitive syntax
// OR
$userDtos = UserDTO::fromModels($users);  // Alternative syntax

// Convert to different formats
$json = $userDto->toJson();
$yaml = $userDto->toYaml();
$csv = $userDto->toCsv();
$xml = $userDto->toXml();
$toml = $userDto->toToml();
$markdown = $userDto->toMarkdownTable();
$phpArray = $userDto->toPhpArray();
$queryString = $userDto->toQueryString();
$msgpack = $userDto->toMessagePack(); // Requires msgpack extension

// Collection exports
$csvData = UserDTO::collectionToCsv($users);
$xmlData = UserDTO::collectionToXml($users);
$yamlData = UserDTO::collectionToYaml($users);
$markdownTable = UserDTO::collectionToMarkdownTable($users);

// Filter data
$publicData = $userDto->only(['name', 'email']);
$safeData = $userDto->except(['password']);
```

### 🛠️ **DtoUtilities** - Utility Methods
```php
// Introspect properties
$properties = $userDto->getProperties();
$hasEmail = $userDto->hasProperty('email');
$email = $userDto->getProperty('email');

// Create variations
$updatedDto = $userDto->with(['name' => 'New Name']);
$areEqual = $userDto->equals($otherDto);
```

**💡 Want to learn more about these traits?**
Check out our [**Traits Guide**](../docs/TRAITS_GUIDE.md) for comprehensive documentation and examples!

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

3. **Use in your application** with built-in traits:
   ```php
   use App\DTOs\UserDTO;
   
   // Create DTO from model
   $userDto = UserDTO::fromModel($user);
   
   // Use ValidatesData trait
   if (UserDTO::passes($data)) {
       $validated = UserDTO::validate($data);
   }
   
   // Use ConvertsData trait - Two ways:
   $userDtos = UserDTO::collection($users);  // Intuitive syntax
   // OR
   $userDtos = UserDTO::fromModels($users);  // Alternative syntax
   
   // Convert to different formats
   $json = $userDto->toJson();
   $publicData = $userDto->only(['name', 'email']);
   
   // Use DtoUtilities trait
   $properties = $userDto->getProperties();
   $updatedDto = $userDto->with(['name' => 'New Name']);
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

### API Controller Usage
See [`api-controller-example.php`](api-controller-example.php) for comprehensive examples of using DTOs in API controllers for:
- Collection management similar to Laravel Resources
- Pagination with DTOs
- Filtering and searching
- Error handling with validation
- **NEW: Modern export formats** (CSV, XML, YAML, TOML, Markdown, MessagePack, Collection - 9 formats total)

### Export Formats Usage
See [`export-formats-example.php`](export-formats-example.php) for comprehensive examples of:
- **Single DTO exports** in 9 different formats (JSON, YAML, CSV, XML, TOML, Markdown, PHP Array, Query String, MessagePack, Collection)
- **Collection exports** in multiple formats
- **Real-world controller usage** with format-based responses
- **Performance considerations** and format recommendations
- **Extension requirements** for advanced formats

### Collection Methods Usage
See [`collection-methods-example.php`](collection-methods-example.php) for comprehensive examples of:
- **New collection() method** - intuitive alias for fromModels()
- **DtoCollection advanced features** - API resource format, field filtering, pagination
- **Laravel Collection compatibility** - all standard collection methods work
- **Practical controller examples** - analytics, filtering, exporting
- **Migration from Laravel Resources** - comparison and upgrade path

## 🎓 Learning Path

### 1. Start with Basic Examples
- [`user.yaml`](user.yaml) - Learn basic field types and validation
- [`advanced-user.yaml`](advanced-user.yaml) - Advanced header configuration and features
- [`product.yaml`](product.yaml) - Understand relationships and options
- [`profile.yaml`](profile.yaml) - See nested DTO preparation

### 2. Explore Enum Features
- [`enum-examples.yaml`](enum-examples.yaml) - Master enum definitions
- [`php-enum-classes.php`](php-enum-classes.php) - Learn PHP enum classes

### 3. Master Advanced Features
- [`nested-order.yaml`](nested-order.yaml) - Complex e-commerce patterns
- [`nested-customer.yaml`](nested-customer.yaml) - Multi-level nesting
- [`circular-category.yaml`](circular-category.yaml) - Circular reference handling

### 4. Learn Practical Usage
- [`api-controller-example.php`](api-controller-example.php) - API controller patterns
- [`export-formats-example.php`](export-formats-example.php) - Export in multiple formats
- [`export-interface-example.php`](export-interface-example.php) - Complete export interface guide
- [`collection-methods-example.php`](collection-methods-example.php) - Collection management

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
header:
  namespace: App\DTOs\Ecommerce  # Domain-specific organization
  traits:
    - HasTimestamps
    - HasSoftDeletes
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
header:
  traits:
    - HasTimestamps
    - HasSoftDeletes  # Only in production
  namespace: App\DTOs  # Adjust based on environment
```

## 📚 Additional Resources

- 🎯 [**DTO Usage Guide**](../docs/DTO_USAGE_GUIDE.md) - **How to use DTOs in your Laravel application**
- 🎯 [**Traits Guide**](../docs/TRAITS_GUIDE.md) - **Complete guide to ValidatesData, ConvertsData, and DtoUtilities traits**
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