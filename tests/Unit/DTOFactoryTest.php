<?php

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Grazulex\Arc\Attributes\DateProperty;
use Grazulex\Arc\Attributes\NestedProperty;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;

// Test DTOs for Factory functionality
class FactoryAddressDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true, validation: 'max:255')]
    public string $street;

    #[Property(type: 'string', required: true, validation: 'max:100')]
    public string $city;

    #[Property(type: 'string', required: true, validation: 'max:20')]
    public string $postalCode;

    protected function validate(array $data): void
    {
        // Simple validation for testing
        if (empty($data['street'])) {
            throw new \InvalidArgumentException('Street is required');
        }
        if (empty($data['city'])) {
            throw new \InvalidArgumentException('City is required');
        }
        if (empty($data['postalCode'])) {
            throw new \InvalidArgumentException('Postal code is required');
        }
    }
}

class FactoryUserDTO extends LaravelArcDTO
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

    #[NestedProperty(dtoClass: FactoryAddressDTO::class, required: false)]
    public ?FactoryAddressDTO $address;

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
        // Simple validation for testing
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Name is required');
        }
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Valid email is required');
        }
        if (!isset($data['age']) || !is_int($data['age']) || $data['age'] < 0) {
            throw new \InvalidArgumentException('Age must be a positive integer');
        }
    }
}

class FactoryTeamDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $name;

    /**
     * @var array<FactoryUserDTO>
     */
    #[NestedProperty(dtoClass: FactoryUserDTO::class, required: false, isCollection: true)]
    public array $members;

    protected function validate(array $data): void
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Team name is required');
        }
    }
}

describe('DTO Factory Functionality', function () {
    it('can create a factory instance', function () {
        $factory = FactoryUserDTO::factory();
        
        expect($factory)->toBeInstanceOf(\Grazulex\Arc\Contracts\DTOFactoryInterface::class);
    });

    it('can create a DTO with specific attributes', function () {
        $user = FactoryUserDTO::factory()
            ->with('name', 'Test User')
            ->with('email', 'test@example.com')
            ->with('age', 25)
            ->create();

        expect($user)->toBeInstanceOf(FactoryUserDTO::class);
        expect($user->name)->toBe('Test User');
        expect($user->email)->toBe('test@example.com');
        expect($user->age)->toBe(25);
    });

    it('can create a DTO with fake data', function () {
        $user = FactoryUserDTO::factory()->fake()->create();

        expect($user)->toBeInstanceOf(FactoryUserDTO::class);
        expect($user->name)->toBeString();
        expect($user->email)->toBeString();
        expect($user->email)->toMatch('/^[^@]+@[^@]+\.[^@]+$/');
        expect($user->age)->toBeInt();
        expect($user->age)->toBeGreaterThanOrEqual(0);
        expect($user->age)->toBeLessThanOrEqual(150);
    });

    it('can use the quick fake method', function () {
        $user = FactoryUserDTO::fake();

        expect($user)->toBeInstanceOf(FactoryUserDTO::class);
        expect($user->name)->toBeString();
        expect($user->email)->toBeString();
        expect($user->age)->toBeInt();
    });

    it('can create multiple DTOs with fake data', function () {
        $users = FactoryUserDTO::fakeMany(3);

        expect($users)->toBeArray();
        expect($users)->toHaveCount(3);
        
        foreach ($users as $user) {
            expect($user)->toBeInstanceOf(FactoryUserDTO::class);
            expect($user->name)->toBeString();
            expect($user->email)->toBeString();
        }
    });

    it('can override fake data with specific values', function () {
        $user = FactoryUserDTO::fake(['name' => 'Override Name']);

        expect($user->name)->toBe('Override Name');
        expect($user->email)->toBeString(); // Still fake
        expect($user->age)->toBeInt(); // Still fake
    });

    it('can generate fake dates', function () {
        $user = FactoryUserDTO::factory()->fake()->create();

        if ($user->birthDate) {
            expect($user->birthDate)->toBeInstanceOf(Carbon::class);
        }
        
        if ($user->createdAt) {
            expect($user->createdAt)->toBeInstanceOf(CarbonImmutable::class);
        }
    });

    it('can generate fake nested DTOs', function () {
        $user = FactoryUserDTO::factory()->fake()->create();

        if ($user->address) {
            expect($user->address)->toBeInstanceOf(FactoryAddressDTO::class);
            expect($user->address->street)->toBeString();
            expect($user->address->city)->toBeString();
            expect($user->address->postalCode)->toBeString();
        }
    });

    it('can generate fake collections of nested DTOs', function () {
        $team = FactoryTeamDTO::factory()->fake()->create();

        expect($team)->toBeInstanceOf(FactoryTeamDTO::class);
        expect($team->name)->toBeString();
        
        if (!empty($team->members)) {
            expect($team->members)->toBeArray();
            foreach ($team->members as $member) {
                expect($member)->toBeInstanceOf(FactoryUserDTO::class);
            }
        }
    });

    it('respects default values for optional properties', function () {
        $user = FactoryUserDTO::factory()
            ->with('name', 'Test User')
            ->with('email', 'test@example.com')
            ->with('age', 25)
            ->create();

        expect($user->role)->toBe('user'); // Default value
        expect($user->active)->toBeTrue(); // Default value
        expect($user->permissions)->toBe([]); // Default value
    });

    it('can mix manual attributes with fake data', function () {
        $user = FactoryUserDTO::factory()
            ->with('name', 'Manual Name')
            ->fake() // This will not override the manual name
            ->create();

        expect($user->name)->toBe('Manual Name');
        expect($user->email)->toBeString(); // Generated
        expect($user->age)->toBeInt(); // Generated
    });

    it('can create with multiple attributes at once', function () {
        $user = FactoryUserDTO::factory()
            ->withAttributes([
                'name' => 'Batch User',
                'email' => 'batch@example.com',
                'age' => 30,
            ])
            ->create();

        expect($user->name)->toBe('Batch User');
        expect($user->email)->toBe('batch@example.com');
        expect($user->age)->toBe(30);
    });

    it('can generate fake data for specific properties only', function () {
        $user = FactoryUserDTO::factory()
            ->with('name', 'Fixed Name')
            ->with('email', 'fixed@example.com')
            ->fakeOnly(['age'])
            ->create();

        expect($user->name)->toBe('Fixed Name');
        expect($user->email)->toBe('fixed@example.com');
        expect($user->age)->toBeInt();
    });

    it('generates valid email format for each instance', function () {
        $users = FactoryUserDTO::fakeMany(3);

        // Check that all emails are valid format
        foreach ($users as $user) {
            expect($user->email)->toBeString();
            expect($user->email)->toMatch('/^[^@]+@[^@]+\.[^@]+$/');
        }
    });
    
    it('can generate data with unique identifiers', function () {
        $user1 = FactoryUserDTO::fake();
        $user2 = FactoryUserDTO::fake();
        
        // Names contain unique IDs, so should be different
        expect($user1->name)->not->toBe($user2->name);
    });
});

