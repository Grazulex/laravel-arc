<?php

/**
 * Laravel Arc DTO Factory Example - Builder Pattern for DTOs
 * Demonstrates the powerful factory system for generating test data and prototyping.
 */

require_once '../../vendor/autoload.php';

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Grazulex\Arc\Attributes\DateProperty;
use Grazulex\Arc\Attributes\NestedProperty;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;

// Define example DTOs
class ExampleAddressDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true, validation: 'max:255')]
    public string $street;

    #[Property(type: 'string', required: true, validation: 'max:100')]
    public string $city;

    #[Property(type: 'string', required: true, validation: 'max:20')]
    public string $postalCode;

    #[Property(type: 'string', required: true, validation: 'max:100')]
    public string $country;

    protected function validate(array $data): void
    {
        // Simple validation for the example
        foreach (['street', 'city', 'postalCode', 'country'] as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("{$field} is required");
            }
        }
    }
}

class ExampleUserDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true, validation: 'max:255')]
    public string $name;

    #[Property(type: 'string', required: true, validation: 'email')]
    public string $email;

    #[Property(type: 'integer', required: true, validation: 'min:0|max:150')]
    public int $age;

    #[DateProperty(required: false, format: 'Y-m-d')]
    public ?Carbon $birthDate;

    #[DateProperty(required: false, immutable: true)]
    public ?CarbonImmutable $createdAt;

    #[NestedProperty(dtoClass: ExampleAddressDTO::class, required: false)]
    public ?ExampleAddressDTO $address;

    #[Property(type: 'string', required: false, default: 'user')]
    public string $role;

    #[Property(type: 'bool', required: false, default: true)]
    public bool $active;

    /**
     * @var array<string>
     */
    #[Property(type: 'array', required: false, default: [])]
    public array $permissions;

    protected function validate(array $data): void
    {
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Name is required');
        }
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Valid email is required');
        }
        if (!isset($data['age']) || !is_int($data['age']) || $data['age'] < 0) {
            throw new InvalidArgumentException('Age must be a positive integer');
        }
    }
}

class ExampleTeamDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $name;

    /**
     * @var array<ExampleUserDTO>
     */
    #[NestedProperty(dtoClass: ExampleUserDTO::class, required: false, isCollection: true)]
    public array $members;

    protected function validate(array $data): void
    {
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Team name is required');
        }
    }
}

echo "🏭 Laravel Arc DTO Factory Example\n";
echo "===================================\n\n";

// 1. Basic Factory Usage
echo "1. 🔧 Basic Factory Usage\n";
echo "------------------------\n";

// Create a user with specific attributes
$user = ExampleUserDTO::factory()
    ->with('name', 'Jean-Marc Strauven')
    ->with('email', 'jean-marc@example.com')
    ->with('age', 30)
    ->create()
;

echo "Created user: {$user->name} ({$user->email}), Age: {$user->age}\n";
echo "Role: {$user->role} (default value)\n\n";

// 2. Fake Data Generation
echo "2. 🎲 Fake Data Generation\n";
echo "-------------------------\n";

$fakeUser = ExampleUserDTO::fake();
echo "Fake user: {$fakeUser->name} ({$fakeUser->email}), Age: {$fakeUser->age}\n";
echo "Role: {$fakeUser->role}, Active: " . ($fakeUser->active ? 'Yes' : 'No') . "\n\n";

// 3. Multiple Instance Generation
echo "3. 👥 Multiple Instance Generation\n";
echo "----------------------------------\n";

$users = ExampleUserDTO::fakeMany(3);
echo 'Generated ' . count($users) . " users:\n";
foreach ($users as $i => $user) {
    echo "  #{$i}: {$user->name} ({$user->email})\n";
}
echo "\n";

// 4. Mixing Manual and Fake Data
echo "4. 🎨 Mixing Manual and Fake Data\n";
echo "---------------------------------\n";

$mixedUser = ExampleUserDTO::factory()
    ->with('name', 'Fixed Name')
    ->fake() // Generate fake data for other fields
    ->create()
;

echo "Mixed user: {$mixedUser->name} (fixed) - {$mixedUser->email} (fake)\n\n";

// 5. Batch Attributes
echo "5. 📦 Batch Attributes\n";
echo "---------------------\n";

$batchUser = ExampleUserDTO::factory()
    ->withAttributes([
        'name' => 'Batch User',
        'email' => 'batch@example.com',
        'role' => 'admin',
        'permissions' => ['read', 'write', 'delete'],
    ])
    ->fakeOnly(['age']) // Only generate fake age
    ->create()
;

echo "Batch user: {$batchUser->name} ({$batchUser->email})\n";
echo "Role: {$batchUser->role}, Permissions: " . implode(', ', $batchUser->permissions) . "\n\n";

// 6. Nested DTOs with Factory
echo "6. 🏠 Nested DTOs with Factory\n";
echo "-----------------------------\n";

$userWithAddress = ExampleUserDTO::factory()
    ->with('name', 'Address User')
    ->with('email', 'address@example.com')
    ->with('age', 25)
    ->fake() // This will generate fake nested data too
    ->create()
;

echo "User with address: {$userWithAddress->name}\n";
if ($userWithAddress->address) {
    echo "  Address: {$userWithAddress->address->street}, {$userWithAddress->address->city}\n";
    echo "  Country: {$userWithAddress->address->country}\n";
}
echo "\n";

// 7. Collections of Nested DTOs
echo "7. 👨‍👩‍👧‍👦 Collections of Nested DTOs\n";
echo "----------------------------------\n";

$team = ExampleTeamDTO::factory()
    ->with('name', 'Development Team')
    ->fake() // This will generate fake members
    ->create()
;

echo "Team: {$team->name}\n";
if (!empty($team->members)) {
    echo 'Members (' . count($team->members) . "):\n";
    foreach ($team->members as $i => $member) {
        echo "  #{$i}: {$member->name} ({$member->email})\n";
    }
} else {
    echo "No members generated\n";
}
echo "\n";

// 8. Date Generation
echo "8. 📅 Date Generation\n";
echo "--------------------\n";

$userWithDates = ExampleUserDTO::factory()
    ->with('name', 'Date User')
    ->with('email', 'dates@example.com')
    ->with('age', 28)
    ->fake()
    ->create()
;

echo "User with dates: {$userWithDates->name}\n";
if ($userWithDates->birthDate) {
    echo "  Birth Date: {$userWithDates->birthDate->format('d/m/Y')} (Carbon)\n";
}
if ($userWithDates->createdAt) {
    echo "  Created At: {$userWithDates->createdAt->format('d/m/Y H:i:s')} (CarbonImmutable)\n";
}
echo "\n";

// 9. Advanced Factory Patterns
echo "9. 🚀 Advanced Factory Patterns\n";
echo "-------------------------------\n";

// Create admin users
$adminUsers = collect([])
    ->push(ExampleUserDTO::fake(['role' => 'admin', 'permissions' => ['read', 'write', 'delete']]))
    ->push(ExampleUserDTO::fake(['role' => 'admin', 'permissions' => ['read', 'write', 'delete']]))
    ->toArray()
;

echo 'Generated ' . count($adminUsers) . " admin users:\n";
foreach ($adminUsers as $i => $admin) {
    echo "  Admin #{$i}: {$admin->name} - Permissions: " . implode(', ', $admin->permissions) . "\n";
}
echo "\n";

// 10. Testing Use Case
echo "10. 🧪 Testing Use Case Simulation\n";
echo "----------------------------------\n";

// Simulate a test scenario
echo "Testing scenario: User registration flow\n";

$testUsers = [
    ExampleUserDTO::fake(['role' => 'user']), // Regular user
    ExampleUserDTO::fake(['role' => 'moderator', 'permissions' => ['read', 'write']]), // Moderator
    ExampleUserDTO::fake(['role' => 'admin', 'permissions' => ['read', 'write', 'delete']]), // Admin
];

foreach ($testUsers as $i => $testUser) {
    echo "  Test User #{$i}: {$testUser->name} - Role: {$testUser->role}\n";
}

echo "\n✅ Factory example completed!\n";
echo "\n💡 Key Benefits:\n";
echo "   - Fast test data generation\n";
echo "   - Consistent fake data patterns\n";
echo "   - Easy prototyping and demos\n";
echo "   - Fluent, readable API\n";
echo "   - Support for nested relationships\n";
echo "   - Respects DTO validation rules\n";
