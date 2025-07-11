# Nested DTO Implementation Guide

This document provides a comprehensive guide to using nested DTOs in Laravel Arc.

## What's New in Nested DTOs

Laravel Arc now includes advanced protection mechanisms for nested DTOs:

### 1. Circular Reference Protection
- Automatically detects and prevents circular references
- Safe handling of self-referencing DTOs (like parent/child relationships)
- No risk of infinite loops during generation

### 2. Depth Limiting
- Default maximum depth of 3 levels
- Automatic fallback to array type when depth limit is reached
- Configurable depth limits for different use cases

### 3. Context Tracking
- Tracks current nesting path during generation
- Prevents performance issues with deeply nested structures
- Maintains generation context across the entire DTO tree

## Quick Start with Nested DTOs

### Basic Nested DTO
```yaml
# user.yaml
header:
  dto: UserDTO
  
fields:
  name:
    type: string
    required: true
  profile:
    type: dto
    dto: ProfileDTO
    required: false
```

### Complex Nested Structure
```yaml
# order.yaml
header:
  dto: OrderDTO
  
fields:
  customer:
    type: dto
    dto: CustomerDTO
    required: true
  billing_address:
    type: dto
    dto: AddressDTO
    required: true
  shipping_address:
    type: dto
    dto: AddressDTO
    required: false
```

## Advanced Features

### Circular Reference Example
```yaml
# category.yaml
header:
  dto: CategoryDTO
  
fields:
  name:
    type: string
    required: true
  parent:
    type: dto
    dto: CategoryDTO  # Safe circular reference
    required: false
```

### Depth Limiting Example
Chain: OrderDTO → AddressDTO → CountryDTO → RegionDTO
- Levels 1-3: Full DTO nesting
- Level 4+: Automatic fallback to array type

## Best Practices

1. **Design for Reasonable Depth**: Keep nesting levels reasonable (3-4 levels max)
2. **Use Meaningful Names**: Use clear DTO class names (e.g., `UserDTO`, not `user`)
3. **Validate Appropriately**: Add validation rules for nested DTOs
4. **Organize by Domain**: Use namespaces to organize DTOs by business domain
5. **Test Complex Structures**: Test deeply nested or circular reference scenarios

## Examples in Action

Check out the examples directory for complete, working examples:
- Simple nested: `examples/user.yaml`
- Complex e-commerce: `examples/nested-order.yaml`
- Circular references: `examples/circular-category.yaml`
- Deep nesting: `examples/nested-country.yaml`

## Migration from Previous Versions

If you're upgrading from a previous version:
1. Update DTO references to use class names (e.g., `UserDTO` instead of `user`)
2. Review any deeply nested structures (may now have depth limits)
3. Test circular reference scenarios (now handled automatically)

## Performance Considerations

- Nested DTOs are validated as arrays at runtime
- Depth limiting prevents performance issues
- Context tracking has minimal overhead
- Generated code is optimized for performance

## Troubleshooting

### Common Issues:

**Problem**: "Too many nested levels"
**Solution**: Reduce nesting depth or increase the limit in `DtoGenerationContext`

**Problem**: "Circular reference detected"
**Solution**: This is actually working correctly - the system prevents infinite loops

**Problem**: "Nested DTO becomes array"
**Solution**: This happens when depth limits are reached - it's a safety feature

For more help, check the examples directory or create an issue on GitHub.