<?php

namespace Grazulex\Arc\Examples;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;

/**
 * Example enum for user status (BackedEnum with string values).
 */
enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
    case SUSPENDED = 'suspended';
}

/**
 * Example enum for user role (UnitEnum without values).
 */
enum UserRole
{
    case ADMIN;
    case USER;
    case MODERATOR;
    case GUEST;
}

/**
 * Example DTO with enum properties.
 */
class EnumExampleDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true, validation: 'max:255')]
    public string $name;

    #[Property(type: 'string', required: true, validation: 'email')]
    public string $email;

    #[Property(type: 'enum', class: UserStatus::class, required: true)]
    public UserStatus $status;

    #[Property(type: 'enum', class: UserRole::class, required: false, default: UserRole::USER)]
    public UserRole $role;

    #[Property(type: 'integer', required: false, validation: 'min:18|max:100')]
    public ?int $age = null;
}
