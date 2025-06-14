<?php

namespace Grazulex\Arc\Examples;

use Carbon\Carbon;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;

/**
 * Example DTO created with:
 * php artisan make:dto User --model=User
 *
 * This shows what would be generated for a typical Laravel User model
 */
class GeneratedUserDTO extends LaravelArcDTO
{
    #[Property(type: 'int', required: false)]
    public ?int $id;

    #[Property(type: 'string', required: false)]
    public ?string $name;

    #[Property(type: 'string', required: false, validation: 'email')]
    public ?string $email;

    #[Property(type: 'date', required: false)]
    public ?Carbon $email_verified_at;

    #[Property(type: 'string', required: false)]
    public ?string $password;

    #[Property(type: 'string', required: false)]
    public ?string $remember_token;

    #[Property(type: 'date', required: false)]
    public ?Carbon $created_at;

    #[Property(type: 'date', required: false)]
    public ?Carbon $updated_at;
}

/**
 * Example UserDTO using the new approach with attributes.
 * No need to define getters/setters or rules manually!
 */
class UserDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true, validation: 'max:255')]
    public string $name;

    #[Property(type: 'string', required: true, validation: 'email')]
    public string $email;

    #[Property(type: 'integer', required: true, validation: 'min:0')]
    public int $age;

    // No need to manually define:
    // - getters/setters (direct access via $user->name)
    // - validation rules (generated automatically)
    // - the rules() method (inherited and automatic)
}
