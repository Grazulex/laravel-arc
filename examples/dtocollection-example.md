# DTOCollection Example

This example demonstrates the native collection support feature that allows chaining operations on DTOs.

## Problem Solved

The issue requested native collection support to enable this syntax:

```php
// Planned feature - native collection support
$users = User::all();
$userDtos = UserDTO::collection($users); // Returns DTOCollection
$userDtos->where('is_active', true)->sortBy('name')
```

## Implementation

### 1. UserDTO Example

```yaml
# database/dto_definitions/user.yaml
header:
  dto: UserDTO
  model: App\Models\User

fields:
  id:
    type: integer
    required: true
  name:
    type: string
    required: true
  email:
    type: string
    required: true
  is_active:
    type: boolean
    default: true
  created_at:
    type: datetime
    required: false

options:
  timestamps: true
  namespace: App\DTOs
```

### 2. Generated Usage

```php
<?php

use App\DTOs\UserDTO;
use App\Models\User;

// Get all users from the database
$users = User::all();

// Convert to DTOCollection - this is the new feature!
$userDtos = UserDTO::collection($users); // Returns DTOCollection

// Chain operations - the key feature requested
$activeUsers = $userDtos
    ->where('is_active', true)
    ->sortBy('name');

// More advanced chaining
$verifiedActiveUsers = $userDtos
    ->where('is_active', true)
    ->whereNotNull('email_verified_at')
    ->sortByDesc('created_at');

// Other DTOCollection methods
$adminUsers = $userDtos
    ->whereIn('role', ['admin', 'super_admin'])
    ->sortBy('name');
```

### 3. Available Methods

All standard Laravel Collection methods plus:

- `where(string $property, mixed $value)` - Filter by property value
- `whereNot(string $property, mixed $value)` - Filter excluding property value
- `whereNull(string $property)` - Filter where property is null
- `whereNotNull(string $property)` - Filter where property is not null
- `whereIn(string $property, array $values)` - Filter where property is in values
- `whereNotIn(string $property, array $values)` - Filter where property is not in values
- `sortBy(string $property)` - Sort by property (ascending)
- `sortByDesc(string $property)` - Sort by property (descending)

### 4. Backward Compatibility

The existing `fromModels()` method continues to work and now returns a DTOCollection instead of a regular Collection:

```php
// Both methods work the same way
$userDtos1 = UserDTO::fromModels($users);
$userDtos2 = UserDTO::collection($users);

// Both return DTOCollection instances
$activeUsers1 = $userDtos1->where('is_active', true);
$activeUsers2 = $userDtos2->where('is_active', true);
```