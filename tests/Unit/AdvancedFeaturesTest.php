<?php

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Exceptions\InvalidDTOException;
use Grazulex\Arc\LaravelArcDTO;

// Test DTOs for advanced features
class TestAddressDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $street;

    #[Property(type: 'string', required: true)]
    public string $city;

    protected function validate(array $data): void
    {
        if (empty($data['street'])) {
            throw new InvalidDTOException('Street is required');
        }
        if (empty($data['city'])) {
            throw new InvalidDTOException('City is required');
        }
    }
}

class TestUserWithDatesDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $name;

    #[Property(type: 'date', required: false, format: 'Y-m-d')]
    public ?Carbon $birthDate;

    #[Property(type: 'date', required: false, immutable: true)]
    public ?CarbonImmutable $createdAt;

    #[Property(type: 'nested', class: TestAddressDTO::class, required: false)]
    public ?TestAddressDTO $address;

    protected function validate(array $data): void
    {
        if (empty($data['name'])) {
            throw new InvalidDTOException('Name is required');
        }
    }
}

class TestTeamDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $name;

    /**
     * @var array<TestUserWithDatesDTO>
     */
    #[Property(type: 'collection', class: TestUserWithDatesDTO::class, required: false)]
    public array $members;

    protected function validate(array $data): void
    {
        if (empty($data['name'])) {
            throw new InvalidDTOException('Team name is required');
        }
    }
}

describe('Date Property Features', function () {
    it('can parse date strings to Carbon instances', function () {
        $user = new TestUserWithDatesDTO([
            'name' => 'Test User',
            'birthDate' => '1990-05-15',
            'createdAt' => '2024-01-15 10:30:00'
        ]);

        expect($user->birthDate)->toBeInstanceOf(Carbon::class);
        expect($user->birthDate->format('Y-m-d'))->toBe('1990-05-15');
        
        expect($user->createdAt)->toBeInstanceOf(CarbonImmutable::class);
        expect($user->createdAt->format('Y-m-d H:i:s'))->toBe('2024-01-15 10:30:00');
    });

    it('can parse unix timestamps to Carbon instances', function () {
        $timestamp = 1705312200; // 2024-01-15 10:30:00 UTC
        
        $user = new TestUserWithDatesDTO([
            'name' => 'Test User',
            'birthDate' => $timestamp
        ]);

        expect($user->birthDate)->toBeInstanceOf(Carbon::class);
        expect($user->birthDate->timestamp)->toBe($timestamp);
    });

    it('preserves existing Carbon instances', function () {
        $date = Carbon::parse('1990-05-15');
        
        $user = new TestUserWithDatesDTO([
            'name' => 'Test User',
            'birthDate' => $date
        ]);

        expect($user->birthDate)->toBe($date);
    });

    it('serializes dates correctly in toArray', function () {
        $user = new TestUserWithDatesDTO([
            'name' => 'Test User',
            'birthDate' => '1990-05-15',
            'createdAt' => '2024-01-15 10:30:00'
        ]);

        $array = $user->toArray();
        
        expect($array['birthDate'])->toBe('1990-05-15');
        expect($array['createdAt'])->toBe('2024-01-15 10:30:00');
    });

    it('handles null dates gracefully', function () {
        $user = new TestUserWithDatesDTO([
            'name' => 'Test User'
        ]);

        expect($user->birthDate)->toBeNull();
        expect($user->createdAt)->toBeNull();
        
        $array = $user->toArray();
        expect($array['birthDate'])->toBeNull();
        expect($array['createdAt'])->toBeNull();
    });
});

describe('Nested Property Features', function () {
    it('can create nested DTOs from arrays', function () {
        $user = new TestUserWithDatesDTO([
            'name' => 'Test User',
            'address' => [
                'street' => '123 Test Street',
                'city' => 'Test City'
            ]
        ]);

        expect($user->address)->toBeInstanceOf(TestAddressDTO::class);
        expect($user->address->street)->toBe('123 Test Street');
        expect($user->address->city)->toBe('Test City');
    });

    it('preserves existing DTO instances', function () {
        $address = new TestAddressDTO([
            'street' => '123 Test Street',
            'city' => 'Test City'
        ]);
        
        $user = new TestUserWithDatesDTO([
            'name' => 'Test User',
            'address' => $address
        ]);

        expect($user->address)->toBe($address);
    });

    it('serializes nested DTOs correctly in toArray', function () {
        $user = new TestUserWithDatesDTO([
            'name' => 'Test User',
            'address' => [
                'street' => '123 Test Street',
                'city' => 'Test City'
            ]
        ]);

        $array = $user->toArray();
        
        expect($array['address'])->toBeArray();
        expect($array['address']['street'])->toBe('123 Test Street');
        expect($array['address']['city'])->toBe('Test City');
    });

    it('handles null nested DTOs gracefully', function () {
        $user = new TestUserWithDatesDTO([
            'name' => 'Test User'
        ]);

        expect($user->address)->toBeNull();
        
        $array = $user->toArray();
        expect($array['address'])->toBeNull();
    });
});

describe('Collection Property Features', function () {
    it('can create collections of nested DTOs', function () {
        $team = new TestTeamDTO([
            'name' => 'Test Team',
            'members' => [
                [
                    'name' => 'Alice',
                    'birthDate' => '1990-01-01'
                ],
                [
                    'name' => 'Bob',
                    'birthDate' => '1985-12-25'
                ]
            ]
        ]);

        expect($team->members)->toBeArray();
        expect($team->members)->toHaveCount(2);
        expect($team->members[0])->toBeInstanceOf(TestUserWithDatesDTO::class);
        expect($team->members[0]->name)->toBe('Alice');
        expect($team->members[1]->name)->toBe('Bob');
    });

    it('serializes collections correctly in toArray', function () {
        $team = new TestTeamDTO([
            'name' => 'Test Team',
            'members' => [
                [
                    'name' => 'Alice',
                    'birthDate' => '1990-01-01'
                ],
                [
                    'name' => 'Bob',
                    'birthDate' => '1985-12-25'
                ]
            ]
        ]);

        $array = $team->toArray();
        
        expect($array['members'])->toBeArray();
        expect($array['members'])->toHaveCount(2);
        expect($array['members'][0]['name'])->toBe('Alice');
        expect($array['members'][0]['birthDate'])->toBe('1990-01-01');
        expect($array['members'][1]['name'])->toBe('Bob');
        expect($array['members'][1]['birthDate'])->toBe('1985-12-25');
    });
});

describe('Combined Advanced Features', function () {
    it('can handle complex nested structures with dates', function () {
        $team = new TestTeamDTO([
            'name' => 'Advanced Team',
            'members' => [
                [
                    'name' => 'Alice Dupont',
                    'birthDate' => '1990-01-01',
                    'createdAt' => '2024-01-15 10:30:00',
                    'address' => [
                        'street' => '123 Alice Street',
                        'city' => 'Brussels'
                    ]
                ]
            ]
        ]);

        $member = $team->members[0];
        expect($member->name)->toBe('Alice Dupont');
        expect($member->birthDate)->toBeInstanceOf(Carbon::class);
        expect($member->createdAt)->toBeInstanceOf(CarbonImmutable::class);
        expect($member->address)->toBeInstanceOf(TestAddressDTO::class);
        expect($member->address->street)->toBe('123 Alice Street');

        $array = $team->toArray();
        expect($array['members'][0]['birthDate'])->toBe('1990-01-01');
        expect($array['members'][0]['createdAt'])->toBe('2024-01-15 10:30:00');
        expect($array['members'][0]['address']['street'])->toBe('123 Alice Street');
    });

    it('can modify nested properties after creation', function () {
        $user = new TestUserWithDatesDTO([
            'name' => 'Test User',
            'birthDate' => '1990-01-01',
            'address' => [
                'street' => '123 Old Street',
                'city' => 'Old City'
            ]
        ]);

        // Modify date
        $user->birthDate = Carbon::parse('1992-05-15');
        expect($user->birthDate->format('Y-m-d'))->toBe('1992-05-15');

        // Modify nested property
        $user->address->street = '456 New Street';
        expect($user->address->street)->toBe('456 New Street');

        // Verify serialization
        $array = $user->toArray();
        expect($array['birthDate'])->toBe('1992-05-15');
        expect($array['address']['street'])->toBe('456 New Street');
    });
});

