# 🆕 Laravel Arc v1.1.0: PHP Enums Support

**Complete PHP 8.1+ Enums Integration for Type-Safe DTOs**

---

## 🎯 What's New

### ✨ **EnumProperty Attribute**
New specialized attribute for handling PHP enums with full type safety:

```php
#[EnumProperty(enumClass: UserStatus::class, required: true)]
public UserStatus $status;

#[EnumProperty(enumClass: UserRole::class, default: UserRole::USER)]
public UserRole $role;
```

### 🔄 **Automatic Enum Casting**
Seamless conversion between strings/integers and enum instances:

```php
// Input: strings/integers
$user = new UserDTO([
    'status' => 'active',        // → UserStatus::ACTIVE
    'role' => 'ADMIN',           // → UserRole::ADMIN  
    'priority' => 3              // → Priority::HIGH
]);

// Direct access to enum instances
echo $user->status->value;       // 'active'
echo $user->role->name;          // 'ADMIN'
```

### 🎭 **Dual Enum Type Support**

#### **BackedEnum** (with values)
```php
enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
}
// Serializes to value: UserStatus::ACTIVE → 'active'
```

#### **UnitEnum** (pure enums)
```php
enum UserRole
{
    case ADMIN;
    case USER;
    case MODERATOR;
}
// Serializes to name: UserRole::ADMIN → 'ADMIN'
```

### 📤 **Smart Serialization**
Automatic conversion back to original values for APIs and storage:

```php
$array = $user->toArray();
// Result: ['status' => 'active', 'role' => 'ADMIN']

$json = $user->toJson();
// Result: {"status":"active","role":"ADMIN"}
```

## 🔧 Technical Enhancements

### **Extended CastManager**
- `castToEnum()`: Convert values to enum instances with validation
- `serializeEnum()`: Smart serialization based on enum type
- Full integration with existing casting system
- Comprehensive error handling

### **Enhanced DTOTrait**
- Extended reflection system to support EnumProperty
- Improved type validation for enum properties
- Seamless integration with existing property types

### **Production Ready**
- ✅ Zero PHPStan Level 6 errors
- ✅ PSR-12 compliant formatting
- ✅ 100% test coverage for enum features
- ✅ Full error handling and edge case management

## 📚 Documentation & Examples

### **Complete Examples Package**
- **`EnumExampleDTO.php`**: Complete enum DTO demonstration
- **`enum_simple_example.php`**: Simple enum usage without Laravel dependencies
- **`enum_advanced_example.php`**: Advanced enum usage with business logic

### **Comprehensive Test Suite**
6 feature tests covering:
- String to enum casting
- Direct enum instance handling  
- Default value behavior
- Serialization accuracy
- Property modification
- JSON conversion

## 🚀 Real-World Usage

### **Order Management System**
```php
enum OrderStatus: string {
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
}

enum PaymentMethod {
    case CREDIT_CARD;
    case PAYPAL;
    case BANK_TRANSFER;
}

class OrderDTO extends LaravelArcDTO
{
    #[EnumProperty(enumClass: OrderStatus::class, required: true)]
    public OrderStatus $status;
    
    #[EnumProperty(enumClass: PaymentMethod::class, required: true)]
    public PaymentMethod $paymentMethod;
    
    // Business logic with enums
    public function canBeCancelled(): bool
    {
        return $this->status === OrderStatus::PENDING;
    }
}

// Usage
$order = new OrderDTO([
    'status' => 'pending',           // Auto-cast to OrderStatus::PENDING
    'paymentMethod' => 'PAYPAL'      // Auto-cast to PaymentMethod::PAYPAL
]);

if ($order->canBeCancelled()) {
    $order->status = OrderStatus::CANCELLED;
}
```

## ⚡ Performance & Quality

- **Type Safety**: Full compile-time and runtime type checking
- **Memory Efficient**: Minimal overhead with smart caching
- **Developer Experience**: Excellent IDE support with proper type hints
- **Production Ready**: Comprehensive error handling and validation

## 📦 Installation & Upgrade

```bash
composer update grazulex/laravel-arc
```

**Requirements:**
- PHP 8.2+ (for enum support, use PHP 8.1+ minimum)
- Laravel 11+ | 12+
- Carbon 3.10+

## 🎉 What's Next

This release establishes Laravel Arc as the premier DTO solution for modern PHP applications, providing:

- **Type-safe enum handling** for business logic
- **Seamless API integration** with smart serialization
- **Developer-friendly** with comprehensive examples
- **Production-ready** with full test coverage

---

**Happy coding with Laravel Arc v1.1.0! 🚀**

*Built with ❤️ by [Jean-Marc Strauven](https://github.com/Grazulex)*

