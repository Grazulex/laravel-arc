<?php

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;
use Grazulex\Arc\Exceptions\InvalidDTOException;

/**
 * Example enum for user status (BackedEnum with string values)
 */
enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
    case SUSPENDED = 'suspended';
}

/**
 * Example enum for user role (UnitEnum without values)
 */
enum UserRole
{
    case ADMIN;
    case USER;
    case MODERATOR;
    case GUEST;
}

/**
 * Example DTO with enum properties
 */
class EnumTestDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $name;

    #[Property(type: 'string', required: true)]
    public string $email;

    #[Property(type: 'enum', class: UserStatus::class, required: true)]
    public UserStatus $status;

    #[Property(type: 'enum', class: UserRole::class, required: false, default: UserRole::USER)]
    public UserRole $role;

    #[Property(type: 'integer', required: false)]
    public ?int $age = null;
    
    protected function validate(array $data): void
    {
        // Simple validation - don't check enum types since they're handled by casting
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Name is required');
        }
        if (empty($data['email'])) {
            throw new \InvalidArgumentException('Email is required');
        }
    }
}

it('can create DTO with enum properties from string values', function () {
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'status' => 'active',
        'role' => 'ADMIN',
        'age' => 30
    ];

    $dto = new EnumTestDTO($data);

    expect($dto->name)->toBe('John Doe');
    expect($dto->email)->toBe('john@example.com');
    expect($dto->status)->toBe(UserStatus::ACTIVE);
    expect($dto->role)->toBe(UserRole::ADMIN);
    expect($dto->age)->toBe(30);
});

it('can handle enum instances directly', function () {
    $data = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'status' => UserStatus::PENDING,
        'role' => UserRole::MODERATOR
    ];

    $dto = new EnumTestDTO($data);

    expect($dto->status)->toBe(UserStatus::PENDING);
    expect($dto->role)->toBe(UserRole::MODERATOR);
});

it('uses default enum value when not provided', function () {
    $data = [
        'name' => 'Bob Smith',
        'email' => 'bob@example.com',
        'status' => 'active'
        // role not provided, should use default
    ];

    $dto = new EnumTestDTO($data);

    expect($dto->role)->toBe(UserRole::USER); // default value
});

it('serializes enums correctly to array', function () {
    $data = [
        'name' => 'Alice Johnson',
        'email' => 'alice@example.com',
        'status' => UserStatus::SUSPENDED,
        'role' => UserRole::ADMIN,
        'age' => 25
    ];

    $dto = new EnumTestDTO($data);
    $array = $dto->toArray();

    expect($array['status'])->toBe('suspended'); // BackedEnum serializes to value
    expect($array['role'])->toBe('ADMIN'); // UnitEnum serializes to name
});

it('can convert to JSON with enum serialization', function () {
    $dto = new EnumTestDTO([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'status' => UserStatus::ACTIVE,
        'role' => UserRole::USER
    ]);

    $json = $dto->toJson();
    $decoded = json_decode($json, true);

    expect($decoded['status'])->toBe('active');
    expect($decoded['role'])->toBe('USER');
});

it('can update enum properties directly', function () {
    $dto = new EnumTestDTO([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'status' => 'active',
        'role' => 'USER'
    ]);

    // Update with enum instance
    $dto->status = UserStatus::INACTIVE;
    expect($dto->status)->toBe(UserStatus::INACTIVE);

    // Update with another enum
    $dto->role = UserRole::ADMIN;
    expect($dto->role)->toBe(UserRole::ADMIN);
});

