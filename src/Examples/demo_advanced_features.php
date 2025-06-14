<?php

/**
 * Simple demonstration of Laravel Arc advanced features.
 * This example works without Laravel framework dependencies.
 */

require_once '../../vendor/autoload.php';

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Grazulex\Arc\Attributes\DateProperty;
use Grazulex\Arc\Attributes\NestedProperty;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;

// Define a simple Address DTO
class DemoAddressDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $street;

    #[Property(type: 'string', required: true)]
    public string $city;

    #[Property(type: 'string', required: true)]
    public string $country;

    protected function validate(array $data): void
    {
        // Simple validation without Laravel
        if (empty($data['street'])) {
            throw new InvalidArgumentException('Street is required');
        }
        if (empty($data['city'])) {
            throw new InvalidArgumentException('City is required');
        }
        if (empty($data['country'])) {
            throw new InvalidArgumentException('Country is required');
        }
    }
}

// Define a User DTO with dates and nested properties
class DemoUserDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $name;

    #[Property(type: 'string', required: true)]
    public string $email;

    #[DateProperty(required: false, format: 'Y-m-d', timezone: 'Europe/Brussels')]
    public ?Carbon $birthDate;

    #[DateProperty(required: false, immutable: true)]
    public ?CarbonImmutable $createdAt;

    #[NestedProperty(dtoClass: DemoAddressDTO::class, required: false)]
    public ?DemoAddressDTO $address;

    /**
     * @var array<string>
     */
    #[Property(type: 'array', required: false, default: [])]
    public array $permissions;

    protected function validate(array $data): void
    {
        // Simple validation without Laravel
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Name is required');
        }
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Valid email is required');
        }
    }
}

echo "🚀 Laravel Arc Advanced Features Demo\n";
echo "=====================================\n\n";

// 1. Creating a user with automatic date conversion and nested DTOs
echo "1. Creating user with dates and nested address...\n";

$userData = [
    'name' => 'Jean-Marc Strauven',
    'email' => 'jean-marc@example.com',
    'birthDate' => '1990-05-15',           // String date
    'createdAt' => time(),                 // Unix timestamp
    'address' => [                         // Nested array -> DTO
        'street' => '123 Rue Example',
        'city' => 'Brussels',
        'country' => 'Belgium',
    ],
    'permissions' => ['read', 'write', 'admin'],
];

$user = new DemoUserDTO($userData);

echo "✅ User created successfully!\n";
echo "   Name: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo '   Birth Date: ' . ($user->birthDate ? $user->birthDate->format('d/m/Y') : 'N/A') . "\n";
echo '   Created At: ' . ($user->createdAt ? $user->createdAt->toDateTimeString() : 'N/A') . "\n";
echo "   Address: {$user->address->street}, {$user->address->city}, {$user->address->country}\n";
echo '   Permissions: ' . implode(', ', $user->permissions) . "\n\n";

// 2. Direct property manipulation
echo "2. Modifying properties directly...\n";

$user->birthDate = Carbon::parse('1992-08-20');
$user->address->city = 'Antwerp';
$user->permissions[] = 'delete';

echo "✅ Properties modified!\n";
echo "   Updated Birth Date: {$user->birthDate->format('d/m/Y')}\n";
echo "   Updated City: {$user->address->city}\n";
echo '   Updated Permissions: ' . implode(', ', $user->permissions) . "\n\n";

// 3. Serialization with automatic formatting
echo "3. Serialization (dates auto-formatted)...\n";

$array = $user->toArray();
echo "✅ Array conversion:\n";
echo "   Birth Date: {$array['birthDate']} (auto-formatted from Carbon)\n";
echo "   Created At: {$array['createdAt']} (auto-formatted from CarbonImmutable)\n";
echo '   Address: ' . json_encode($array['address']) . " (nested DTO serialized)\n\n";

// 4. JSON serialization
echo "4. JSON serialization...\n";
$json = $user->toJson();
echo '✅ JSON: ' . $json . "\n\n";

// 5. Working with null values
echo "5. Handling null values...\n";

$minimalUser = new DemoUserDTO([
    'name' => 'Minimal User',
    'email' => 'minimal@example.com',
    // birthDate, createdAt, address are all null
]);

echo "✅ Minimal user created!\n";
echo '   Birth Date: ' . ($minimalUser->birthDate ? $minimalUser->birthDate->format('d/m/Y') : 'null') . "\n";
echo '   Address: ' . ($minimalUser->address ? 'set' : 'null') . "\n";

$minimalArray = $minimalUser->toArray();
echo '   Serialized birth date: ' . ($minimalArray['birthDate'] ?? 'null') . "\n";
echo '   Serialized address: ' . ($minimalArray['address'] ?? 'null') . "\n\n";

echo "🎉 Demo completed! All advanced features working correctly.\n";
echo "\nKey features demonstrated:\n";
echo "- ✅ Automatic Carbon date transformation\n";
echo "- ✅ Nested DTOs from arrays\n";
echo "- ✅ Direct property access and modification\n";
echo "- ✅ Automatic serialization with proper formatting\n";
echo "- ✅ Null value handling\n";
echo "- ✅ Timezone support\n";
echo "- ✅ Unix timestamp parsing\n";
