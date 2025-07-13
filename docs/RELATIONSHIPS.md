# Relationships Guide

Laravel Arc provides full support for Laravel Eloquent relationships in your DTOs. This guide covers all supported relationship types and how to use them effectively.

## Supported Relationship Types

| Type | Description | Example |
|------|-------------|---------|
| `belongsTo` | Many-to-one relationship | User belongs to Company |
| `hasOne` | One-to-one relationship | User has one Profile |
| `hasMany` | One-to-many relationship | User has many Posts |
| `belongsToMany` | Many-to-many relationship | User belongs to many Roles |

## Important Note

**All examples in this guide use the new trait-based system.** The old `options` section is deprecated. Use `header.traits` instead:

```yaml
# ✅ New format (recommended)
header:
  dto: UserDTO
  model: App\Models\User
  namespace: App\DTOs
  traits:
    - HasTimestamps

# ❌ Old format (deprecated)
options:
  timestamps: true
  namespace: App\DTOs
```

## Basic Relationship Definition

### belongsTo (Many-to-One)

```yaml
# user.yaml
relations:
  company:
    type: belongsTo
    target: App\Models\Company
```

**Generated in UserDTO:**
```php
public function company(): BelongsTo
{
    return $this->belongsTo(Company::class);
}
```

### hasOne (One-to-One)

```yaml
# user.yaml
relations:
  profile:
    type: hasOne
    target: App\Models\Profile
```

**Generated in UserDTO:**
```php
public function profile(): HasOne
{
    return $this->hasOne(Profile::class);
}
```

### hasMany (One-to-Many)

```yaml
# user.yaml
relations:
  posts:
    type: hasMany
    target: App\Models\Post
```

**Generated in UserDTO:**
```php
public function posts(): HasMany
{
    return $this->hasMany(Post::class);
}
```

### belongsToMany (Many-to-Many)

```yaml
# user.yaml
relations:
  roles:
    type: belongsToMany
    target: App\Models\Role
```

**Generated in UserDTO:**
```php
public function roles(): BelongsToMany
{
    return $this->belongsToMany(Role::class);
}
```

## Complete Examples

### User with Multiple Relationships

```yaml
# database/dto_definitions/user.yaml
header:
  dto: UserDTO
  table: users
  model: App\Models\User

fields:
  id:
    type: uuid
    required: true
  name:
    type: string
    required: true
    rules: [min:2, max:100]
  email:
    type: string
    required: true
    rules: [email, unique:users]
  company_id:
    type: uuid
    required: false

relations:
  # Many-to-one: User belongs to Company
  company:
    type: belongsTo
    target: App\Models\Company
    
  # One-to-one: User has one Profile
  profile:
    type: hasOne
    target: App\Models\Profile
    
  # One-to-many: User has many Posts
  posts:
    type: hasMany
    target: App\Models\Post
    
  # Many-to-many: User belongs to many Roles
  roles:
    type: belongsToMany
    target: App\Models\Role

options:
  timestamps: true
  namespace: App\DTOs
```

### Company with Employees

```yaml
# database/dto_definitions/company.yaml
header:
  dto: CompanyDTO
  table: companies
  model: App\Models\Company

fields:
  id:
    type: uuid
    required: true
  name:
    type: string
    required: true
    rules: [min:2, max:255]
  email:
    type: string
    required: true
    rules: [email, unique:companies]
  website:
    type: string
    rules: [url, nullable]

relations:
  # One-to-many: Company has many Users (employees)
  employees:
    type: hasMany
    target: App\Models\User
    
  # One-to-one: Company has one primary contact
  primary_contact:
    type: hasOne
    target: App\Models\User

options:
  timestamps: true
  namespace: App\DTOs
```

### E-commerce Example

```yaml
# database/dto_definitions/order.yaml
header:
  dto: OrderDTO
  table: orders
  model: App\Models\Order

fields:
  id:
    type: uuid
    required: true
  order_number:
    type: string
    required: true
    rules: [unique:orders]
  customer_id:
    type: uuid
    required: true
  status:
    type: enum
    class: App\Enums\OrderStatus
    default: pending
  total_amount:
    type: decimal
    rules: [numeric, min:0]

relations:
  # Many-to-one: Order belongs to Customer
  customer:
    type: belongsTo
    target: App\Models\User
    
  # One-to-many: Order has many Items
  items:
    type: hasMany
    target: App\Models\OrderItem
    
  # One-to-one: Order has one payment
  payment:
    type: hasOne
    target: App\Models\Payment

options:
  timestamps: true
  namespace: App\DTOs\Ecommerce
```

## Relationship with Nested DTOs

You can combine relationships with nested DTOs for complex data structures:

```yaml
# database/dto_definitions/user.yaml
header:
  dto: UserDTO
  table: users
  model: App\Models\User

fields:
  id:
    type: uuid
    required: true
  name:
    type: string
    required: true
  email:
    type: string
    required: true
    rules: [email, unique:users]
  
  # Nested DTO for profile data
  profile:
    type: dto
    dto: ProfileDTO
    required: false

relations:
  # Eloquent relationship for posts
  posts:
    type: hasMany
    target: App\Models\Post
    
  # Eloquent relationship for company
  company:
    type: belongsTo
    target: App\Models\Company

options:
  timestamps: true
  namespace: App\DTOs
```

## Advanced Relationship Examples

### Blog System

```yaml
# database/dto_definitions/post.yaml
header:
  dto: PostDTO
  table: posts
  model: App\Models\Post

fields:
  id:
    type: uuid
    required: true
  title:
    type: string
    required: true
    rules: [min:2, max:255]
  slug:
    type: string
    required: true
    rules: [unique:posts]
  content:
    type: text
    required: true
  status:
    type: enum
    class: App\Enums\PostStatus
    default: draft
  published_at:
    type: datetime
    required: false
  author_id:
    type: uuid
    required: true

relations:
  # Many-to-one: Post belongs to Author (User)
  author:
    type: belongsTo
    target: App\Models\User
    
  # One-to-many: Post has many Comments
  comments:
    type: hasMany
    target: App\Models\Comment
    
  # Many-to-many: Post belongs to many Categories
  categories:
    type: belongsToMany
    target: App\Models\Category
    
  # Many-to-many: Post belongs to many Tags
  tags:
    type: belongsToMany
    target: App\Models\Tag

options:
  timestamps: true
  namespace: App\DTOs\Blog
```

### Inventory Management

```yaml
# database/dto_definitions/product.yaml
header:
  dto: ProductDTO
  table: products
  model: App\Models\Product

fields:
  id:
    type: uuid
    required: true
  name:
    type: string
    required: true
    rules: [min:2, max:255]
  sku:
    type: string
    required: true
    rules: [unique:products]
  price:
    type: decimal
    rules: [numeric, min:0]
  category_id:
    type: uuid
    required: true
  supplier_id:
    type: uuid
    required: true

relations:
  # Many-to-one: Product belongs to Category
  category:
    type: belongsTo
    target: App\Models\Category
    
  # Many-to-one: Product belongs to Supplier
  supplier:
    type: belongsTo
    target: App\Models\Supplier
    
  # One-to-many: Product has many Stock movements
  stock_movements:
    type: hasMany
    target: App\Models\StockMovement
    
  # One-to-many: Product has many Reviews
  reviews:
    type: hasMany
    target: App\Models\Review
    
  # Many-to-many: Product belongs to many Orders (through OrderItem)
  orders:
    type: belongsToMany
    target: App\Models\Order

options:
  timestamps: true
  namespace: App\DTOs\Inventory
```

## Best Practices

### 1. Use Descriptive Relationship Names

```yaml
# Good
relations:
  author:
    type: belongsTo
    target: App\Models\User
    
  primary_contact:
    type: hasOne
    target: App\Models\User

# Less clear
relations:
  user:
    type: belongsTo
    target: App\Models\User
    
  contact:
    type: hasOne
    target: App\Models\User
```

### 2. Include Foreign Key Fields

```yaml
fields:
  company_id:
    type: uuid
    required: false
  
relations:
  company:
    type: belongsTo
    target: App\Models\Company
```

### 3. Use Nested DTOs for Complex Data

```yaml
# For detailed profile information
profile:
  type: dto
  dto: ProfileDTO
  required: false

# For simple relationships
relations:
  company:
    type: belongsTo
    target: App\Models\Company
```

### 4. Group Related DTOs by Domain

```yaml
# E-commerce DTOs
options:
  namespace: App\DTOs\Ecommerce

# Blog DTOs  
options:
  namespace: App\DTOs\Blog

# User management DTOs
options:
  namespace: App\DTOs\User
```

## Common Patterns

### User-Post-Comment Hierarchy

```yaml
# User DTO
relations:
  posts:
    type: hasMany
    target: App\Models\Post
  comments:
    type: hasMany
    target: App\Models\Comment

# Post DTO
relations:
  author:
    type: belongsTo
    target: App\Models\User
  comments:
    type: hasMany
    target: App\Models\Comment

# Comment DTO
relations:
  author:
    type: belongsTo
    target: App\Models\User
  post:
    type: belongsTo
    target: App\Models\Post
```

### E-commerce Order-Item-Product Chain

```yaml
# Order DTO
relations:
  customer:
    type: belongsTo
    target: App\Models\User
  items:
    type: hasMany
    target: App\Models\OrderItem

# OrderItem DTO
relations:
  order:
    type: belongsTo
    target: App\Models\Order
  product:
    type: belongsTo
    target: App\Models\Product

# Product DTO
relations:
  category:
    type: belongsTo
    target: App\Models\Category
  order_items:
    type: hasMany
    target: App\Models\OrderItem
```

## Troubleshooting

### Common Issues

**Missing Foreign Key Fields**:
```yaml
# Ensure foreign key fields are included
fields:
  company_id:
    type: uuid
    required: false
    
relations:
  company:
    type: belongsTo
    target: App\Models\Company
```

**Circular Dependencies**:
```yaml
# Use nested DTOs carefully to avoid circular references
# Laravel Arc has built-in protection, but design carefully
```

**Namespace Issues**:
```yaml
# Use full class names for targets
relations:
  company:
    type: belongsTo
    target: App\Models\Company  # Full namespace
```

## See Also

- [YAML Schema Documentation](YAML_SCHEMA.md)
- [Field Types Reference](FIELD_TYPES.md)
- [Nested DTO Guide](NESTED_DTO_GUIDE.md)
- [Examples](../examples/README.md)