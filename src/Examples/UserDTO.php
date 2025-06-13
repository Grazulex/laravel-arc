<?php

namespace Grazulex\Arc\Examples;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;

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
