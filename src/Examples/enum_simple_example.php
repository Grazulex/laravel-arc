<?php

/**
 * Simple enum usage example with Laravel Arc DTOs
 * Demonstrates enum casting and serialization without Laravel validation.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Grazulex\Arc\Attributes\EnumProperty;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;

// User status enum (BackedEnum)
enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
}

// User role enum (UnitEnum)
enum UserRole
{
    case ADMIN;
    case USER;
    case MODERATOR;
}

// Simple user DTO with enums (no validation to avoid Laravel dependencies)
class SimpleUserDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $name;

    #[Property(type: 'string', required: true)]
    public string $email;

    #[EnumProperty(enumClass: UserStatus::class, required: true)]
    public UserStatus $status;

    #[EnumProperty(enumClass: UserRole::class, default: UserRole::USER)]
    public UserRole $role;

    // Override to skip Laravel validation in examples
    protected function validate(array $data): void
    {
        // Skip validation for simple example
    }
}

echo "🎯 Simple Enum Usage Example with Laravel Arc\n";
echo "============================================\n\n";

// Example 1: Create user with string values (automatic enum casting)
echo "📝 Creating user from form data (strings)...\n";
$userData = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'status' => 'active',        // String -> UserStatus::ACTIVE
    'role' => 'ADMIN',            // String -> UserRole::ADMIN
];

$user = new SimpleUserDTO($userData);

echo "✅ User created successfully!\n";
echo "   Name: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   Status: {$user->status->value} (enum: " . $user->status::class . ")\n";
echo "   Role: {$user->role->name} (enum: " . $user->role::class . ")\n\n";

// Example 2: Create user with enum instances
echo "🔧 Creating user with enum instances...\n";
$adminUser = new SimpleUserDTO([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'status' => UserStatus::ACTIVE,     // Direct enum instance
    'role' => UserRole::ADMIN,           // Direct enum instance
]);

echo "✅ Admin user created!\n";
echo '   Status is active: ' . ($adminUser->status === UserStatus::ACTIVE ? 'Yes' : 'No') . "\n";
echo '   Is admin role: ' . ($adminUser->role === UserRole::ADMIN ? 'Yes' : 'No') . "\n\n";

// Example 3: Default enum value
echo "🎨 Creating user with default role...\n";
$defaultUser = new SimpleUserDTO([
    'name' => 'Default User',
    'email' => 'default@example.com',
    'status' => 'pending',
    // role not provided, will use default UserRole::USER
]);

echo "✅ Default user created!\n";
echo "   Default role: {$defaultUser->role->name}\n";
echo "   Status: {$defaultUser->status->value}\n\n";

// Example 4: Serialization
echo "📤 Serializing users to array/JSON...\n";
$userArray = $user->toArray();
echo "User array format:\n";
echo "   status: '{$userArray['status']}' (BackedEnum serialized to value)\n";
echo "   role: '{$userArray['role']}' (UnitEnum serialized to name)\n\n";

$userJson = $user->toJson();
echo "User JSON format:\n";
echo "   {$userJson}\n\n";

// Example 5: Property access and modification
echo "🔄 Modifying enum properties...\n";
echo "Before: Status = {$user->status->value}, Role = {$user->role->name}\n";

// Direct enum assignment
$user->status = UserStatus::INACTIVE;
$user->role = UserRole::MODERATOR;

echo "After:  Status = {$user->status->value}, Role = {$user->role->name}\n\n";

// Example 6: Working with multiple users
echo "👥 Working with multiple users...\n";
$users = [$user, $adminUser, $defaultUser];

foreach ($users as $index => $u) {
    echo 'User ' . ($index + 1) . ": {$u->name}\n";
    echo "   📧 {$u->email}\n";
    echo "   📊 Status: {$u->status->value} | Role: {$u->role->name}\n";
    echo '   🎨 Is admin: ' . ($u->role === UserRole::ADMIN ? 'Yes' : 'No') . "\n";
    echo '   ✅ Is active: ' . ($u->status === UserStatus::ACTIVE ? 'Yes' : 'No') . "\n\n";
}

echo "🎉 Simple enum example completed!\n";
echo "\nKey features demonstrated:\n";
echo "• ✅ Automatic string/name to enum casting\n";
echo "• ✅ Direct enum instance assignment\n";
echo "• ✅ Default enum values\n";
echo "• ✅ Enum serialization (BackedEnum → value, UnitEnum → name)\n";
echo "• ✅ Type-safe enum comparisons\n";
echo "• ✅ Direct property access with enums\n";
