<?php

namespace Grazulex\Arc\Examples;

/**
 * Advanced usage example showing dates and nested DTOs.
 */

require_once '../../vendor/autoload.php';

use Carbon\Carbon;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;

echo "=== Laravel Arc Advanced Features Example ===\n\n";

// 1. Creating a user with nested address and company
$userData = [
    'name' => 'Jean-Marc Strauven',
    'email' => 'jean-marc@example.com',
    'birthDate' => '1990-05-15',
    'createdAt' => '2024-01-15 10:30:00',
    'lastLoginAt' => time(), // Unix timestamp
    'address' => [
        'street' => '123 Rue Example',
        'city' => 'Brussels',
        'postalCode' => '1000',
        'country' => 'Belgium',
    ],
    'company' => [
        'name' => 'Tech Solutions Inc.',
        'email' => 'contact@techsolutions.com',
        'address' => [
            'street' => '456 Business Ave',
            'city' => 'Brussels',
            'postalCode' => '1050',
            'country' => 'Belgium',
        ],
    ],
    'permissions' => ['read', 'write', 'admin'],
    'isActive' => true,
];

$user = new UserAdvancedDTO($userData);

echo "User created with automatic date parsing and nested DTOs:\n";
echo "- Name: {$user->name}\n";
echo "- Email: {$user->email}\n";
echo '- Birth Date: ' . ($user->birthDate ? $user->birthDate->format('d/m/Y') : 'N/A') . "\n";
echo '- Created At: ' . ($user->createdAt ? $user->createdAt->toDateTimeString() : 'N/A') . "\n";
echo '- Last Login: ' . ($user->lastLoginAt ? $user->lastLoginAt->toDateTimeString() : 'N/A') . "\n";
echo "- Address: {$user->address->street}, {$user->address->city}\n";
echo "- Company: {$user->company->name}\n";
echo '- Permissions: ' . implode(', ', $user->permissions) . "\n";
echo '- Active: ' . ($user->isActive ? 'Yes' : 'No') . "\n\n";

// 2. Direct property access and modification
echo "Direct property manipulation:\n";
$user->birthDate = Carbon::parse('1992-08-20');
$user->lastLoginAt = Carbon::now();
$user->address->city = 'Antwerp';
$user->company->name = 'Advanced Tech Solutions';

echo "- Updated Birth Date: {$user->birthDate->format('d/m/Y')}\n";
echo "- Updated Last Login: {$user->lastLoginAt->format('d/m/Y H:i:s')}\n";
echo "- Updated City: {$user->address->city}\n";
echo "- Updated Company: {$user->company->name}\n\n";

// 3. Array conversion with proper serialization
echo "Array conversion (dates are automatically formatted):\n";
$array = $user->toArray();
print_r($array);

echo "\nJSON serialization:\n";
echo $user->toJson() . "\n\n";

// 4. Working with collections of nested DTOs
echo "=== Collections Example ===\n\n";

// Create a DTO that handles collections
class TeamExample extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $name;

    /**
     * @var array<UserAdvancedDTO>
     */
    #[Property(type: 'collection', class: UserAdvancedDTO::class, required: false)]
    public array $members;

    protected function validate(array $data): void
    {
        // Custom validation
    }
}

$teamData = [
    'name' => 'Development Team',
    'members' => [
        [
            'name' => 'Alice Dupont',
            'email' => 'alice@example.com',
            'birthDate' => '1988-03-10',
            'permissions' => ['read', 'write'],
        ],
        [
            'name' => 'Bob Martin',
            'email' => 'bob@example.com',
            'birthDate' => '1985-12-25',
            'permissions' => ['read', 'write', 'admin'],
        ],
    ],
];

$team = new team_example($teamData);

echo "Team: {$team->name}\n";
echo "Members:\n";
foreach ($team->members as $member) {
    echo "  - {$member->name} ({$member->email})\n";
    if ($member->birthDate) {
        echo "    Born: {$member->birthDate->format('d/m/Y')}\n";
    }
}

echo "\nTeam serialized to array:\n";
print_r($team->toArray());
