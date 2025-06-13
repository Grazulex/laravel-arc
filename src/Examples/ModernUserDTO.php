<?php

namespace Grazulex\Arc\Examples;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;

class ModernUserDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true, validation: 'max:255')]
    public string $name;

    #[Property(type: 'string', required: true, validation: 'email')]
    public string $email;

    #[Property(type: 'integer', required: true, validation: 'min:0|max:150')]
    public int $age;

    #[Property(type: 'string', required: false, default: 'user')]
    public string $role;

    #[Property(type: 'boolean', required: false, default: true)]
    public bool $active;

    /**
     * @var array<string>
     */
    #[Property(type: 'array', required: false, default: [])]
    public array $permissions;

    // No need to manually define getters/setters!
    // Methods getName(), setName(), getEmail(), setEmail(), etc.
    // are automatically available via __call() magic
}
