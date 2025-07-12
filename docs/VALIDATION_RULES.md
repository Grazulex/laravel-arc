# Validation Rules Reference

Laravel Arc provides comprehensive validation support through seamless integration with Laravel's validation system. This guide covers all validation features and best practices.

## Overview

Laravel Arc automatically applies validation rules defined in your YAML files, supporting:
- **Laravel's built-in validation rules**
- **Custom validation rules**
- **Enum-specific validation**
- **Nested DTO validation**
- **Conditional validation**

## Basic Validation Rules

### String Validation

```yaml
fields:
  name:
    type: string
    required: true
    rules: [min:2, max:100]
    
  email:
    type: string
    required: true
    rules: [email, unique:users]
    
  slug:
    type: string
    rules: [alpha_dash, unique:posts]
    
  password:
    type: string
    required: true
    rules: [min:8, confirmed]
    
  website:
    type: string
    rules: [url, nullable]
    
  phone:
    type: string
    rules: [phone:US, nullable]
```

### Numeric Validation

```yaml
fields:
  age:
    type: integer
    rules: [min:0, max:150]
    
  price:
    type: decimal
    rules: [numeric, min:0, max:999999.99]
    
  rating:
    type: float
    rules: [numeric, between:0,5]
    
  quantity:
    type: integer
    rules: [integer, min:1]
    
  percentage:
    type: float
    rules: [numeric, between:0,100]
```

### Date and Time Validation

```yaml
fields:
  birth_date:
    type: date
    rules: [date, before:today]
    
  appointment_date:
    type: datetime
    rules: [date, after:now]
    
  start_time:
    type: time
    rules: [date_format:H:i]
    
  event_date:
    type: datetime
    rules: [date, after:tomorrow, before:2025-12-31]
```

### Array Validation

```yaml
fields:
  tags:
    type: array
    rules: [array, min:1, max:10]
    
  skills:
    type: array
    rules: [array, distinct]
    
  categories:
    type: array
    rules: [array, 'array:id,name']
    
  metadata:
    type: json
    rules: [json]
```

## Enum Validation

Laravel Arc provides specialized validation for enums with automatic rule generation.

### Traditional Enum Validation

```yaml
fields:
  status:
    type: enum
    values: [draft, published, archived]
    default: draft
    rules: [in:draft,published,archived]  # Automatically added
```

### PHP Enum Class Validation

```yaml
fields:
  # String enum
  status:
    type: enum
    class: App\Enums\Status
    default: draft
    # Laravel automatically adds: enum:App\Enums\Status
    
  # Int enum
  priority:
    type: enum
    class: App\Enums\Priority
    default: 2
    # Laravel automatically adds: enum:App\Enums\Priority
    
  # With additional rules
  category:
    type: enum
    class: App\Enums\Category
    required: true
    rules: [required, 'enum:App\Enums\Category']
```

### Custom Enum Validation Rules

Laravel Arc provides specialized enum validation rules:

```yaml
fields:
  status:
    type: enum
    class: App\Enums\Status
    rules:
      - in_enum        # Alternative to enum: rule
      - enum_exists    # Validates enum class existence
      - required
```

For detailed enum validation documentation, see [Enum Custom Rules](ENUM_CUSTOM_RULES.md).

## Nested DTO Validation

Nested DTOs are validated as arrays with optional custom rules:

```yaml
fields:
  profile:
    type: dto
    dto: ProfileDTO
    required: true
    rules: [array, min:1]
    
  addresses:
    type: array
    rules: [array, min:1, max:5]
    
  billing_address:
    type: dto
    dto: AddressDTO
    required: false
    rules: [array, 'nullable']
```

## Advanced Validation Patterns

### Conditional Validation

```yaml
fields:
  type:
    type: enum
    class: App\Enums\UserType
    default: regular
    
  company_name:
    type: string
    rules: [required_if:type,business, max:255]
    
  vat_number:
    type: string
    rules: [required_if:type,business, regex:/^[A-Z]{2}[0-9]{8,12}$/]
```

### File Upload Validation

```yaml
fields:
  avatar:
    type: string
    rules: [image, mimes:jpeg,png,gif, max:2048]
    
  document:
    type: string
    rules: [file, mimes:pdf,doc,docx, max:10240]
    
  attachments:
    type: array
    rules: [array, max:5]
```

### Complex Validation Rules

```yaml
fields:
  coordinates:
    type: array
    rules: [array, size:2, 'regex:/^-?\d+\.?\d*$/']
    
  social_security:
    type: string
    rules: [regex:/^\d{3}-\d{2}-\d{4}$/, unique:users]
    
  credit_card:
    type: string
    rules: [regex:/^\d{4}-\d{4}-\d{4}-\d{4}$/, luhn]
```

## Custom Validation Rules

### Using Custom Rule Classes

```yaml
fields:
  custom_field:
    type: string
    rules: [required, 'App\Rules\CustomRule']
    
  business_hours:
    type: json
    rules: [json, 'App\Rules\ValidBusinessHours']
    
  color_code:
    type: string
    rules: [regex:/^#[0-9A-F]{6}$/i, 'App\Rules\ValidColorCode']
```

### Closure-Based Rules

```yaml
fields:
  username:
    type: string
    rules: 
      - required
      - min:3
      - max:20
      - alpha_dash
      - unique:users
      - function($attribute, $value, $fail) {
          if (str_contains(strtolower($value), 'admin')) {
              $fail('Username cannot contain "admin"');
          }
        }
```

## Database Validation

### Unique Constraints

```yaml
fields:
  email:
    type: string
    rules: [email, unique:users]
    
  slug:
    type: string
    rules: [alpha_dash, unique:posts,slug]
    
  # Unique with conditions
  code:
    type: string
    rules: [unique:products,code,NULL,id,deleted_at,NULL]
```

### Exists Validation

```yaml
fields:
  category_id:
    type: uuid
    rules: [exists:categories,id]
    
  user_id:
    type: uuid
    rules: [exists:users,id,deleted_at,NULL]
    
  # Exists with conditions
  parent_id:
    type: uuid
    rules: [exists:categories,id,type,parent]
```

## Validation Groups and Contexts

### Context-Specific Validation

```yaml
fields:
  password:
    type: string
    rules: [min:8, confirmed]  # For creation
    
  current_password:
    type: string
    rules: [required, current_password]  # For updates
    
  new_password:
    type: string
    rules: [min:8, confirmed, different:current_password]
```

### Validation Scenarios

```yaml
# user-create.yaml
fields:
  email:
    type: string
    required: true
    rules: [email, unique:users]
    
  password:
    type: string
    required: true
    rules: [min:8, confirmed]

# user-update.yaml  
fields:
  email:
    type: string
    required: true
    rules: [email, unique:users,email,{id}]
    
  password:
    type: string
    required: false
    rules: [min:8, confirmed, nullable]
```

## Best Practices

### 1. Use Appropriate Rule Combinations

```yaml
# Good: Comprehensive validation
email:
  type: string
  required: true
  rules: [email, max:255, unique:users]

# Avoid: Insufficient validation
email:
  type: string
  rules: [email]
```

### 2. Leverage Laravel's Built-in Rules

```yaml
# Good: Use Laravel's rules
price:
  type: decimal
  rules: [numeric, min:0, max:999999.99]

# Avoid: Custom regex when built-in exists
price:
  type: string
  rules: [regex:/^\d+\.\d{2}$/]
```

### 3. Use Enum Classes for Better Validation

```yaml
# Recommended: PHP enum with automatic validation
status:
  type: enum
  class: App\Enums\Status
  default: draft

# Less preferred: Manual validation
status:
  type: enum
  values: [draft, published, archived]
  rules: [in:draft,published,archived]
```

### 4. Validate Nested Data Appropriately

```yaml
# Good: Validate nested structure
profile:
  type: dto
  dto: ProfileDTO
  required: true
  rules: [array, min:1]

# Avoid: No validation for nested data
profile:
  type: dto
  dto: ProfileDTO
  required: true
```

## Error Handling

### Custom Error Messages

```yaml
fields:
  age:
    type: integer
    rules: [min:18, max:120]
    messages:
      min: 'You must be at least 18 years old'
      max: 'Age cannot exceed 120 years'
```

### Validation Attributes

```yaml
fields:
  first_name:
    type: string
    rules: [required, min:2]
    attributes:
      first_name: 'First Name'
```

## Performance Considerations

### Optimizing Validation Rules

```yaml
# Efficient: Order rules by performance
email:
  type: string
  rules: [required, email, max:255, unique:users]

# Less efficient: Expensive rules first
email:
  type: string
  rules: [unique:users, required, email, max:255]
```

### Conditional Expensive Rules

```yaml
# Use sometimes for expensive operations
vat_number:
  type: string
  rules: [sometimes, required_if:type,business, 'App\Rules\ValidVatNumber']
```

## Testing Validation

### Unit Testing Validation Rules

```php
// Test DTO validation
public function test_user_dto_validation(): void
{
    $userDto = new UserDTO([
        'name' => 'a',  // Too short
        'email' => 'invalid-email',  // Invalid format
        'age' => 150,  // Too old
    ]);
    
    $this->expectException(ValidationException::class);
    $userDto->validate();
}
```

### Integration Testing

```php
// Test with Laravel validation
public function test_user_creation_validation(): void
{
    $response = $this->post('/users', [
        'name' => 'a',
        'email' => 'invalid-email',
        'age' => 150,
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'age']);
}
```

## Common Validation Patterns

### E-commerce Validation

```yaml
# Product validation
fields:
  sku:
    type: string
    required: true
    rules: [alpha_dash, unique:products, max:50]
    
  price:
    type: decimal
    required: true
    rules: [numeric, min:0, max:999999.99]
    
  category_id:
    type: uuid
    required: true
    rules: [exists:categories,id]
    
  stock_quantity:
    type: integer
    rules: [integer, min:0]
    
  is_active:
    type: boolean
    default: true
    rules: [boolean]
```

### User Management Validation

```yaml
# User profile validation
fields:
  username:
    type: string
    required: true
    rules: [alpha_dash, min:3, max:20, unique:users]
    
  email:
    type: string
    required: true
    rules: [email, max:255, unique:users]
    
  phone:
    type: string
    rules: [phone:US, nullable]
    
  birth_date:
    type: date
    rules: [date, before:today, after:1900-01-01]
    
  avatar:
    type: string
    rules: [url, nullable]
```

### Financial Validation

```yaml
# Payment validation
fields:
  amount:
    type: decimal
    required: true
    rules: [numeric, min:0.01, max:999999.99]
    
  currency:
    type: enum
    class: App\Enums\Currency
    default: USD
    
  payment_method:
    type: enum
    class: App\Enums\PaymentMethod
    required: true
    
  transaction_id:
    type: string
    rules: [alpha_num, unique:transactions]
```

## See Also

- [Field Types Reference](FIELD_TYPES.md)
- [Enum Custom Rules](ENUM_CUSTOM_RULES.md)
- [Getting Started Guide](GETTING_STARTED.md)
- [Examples Collection](../examples/README.md)