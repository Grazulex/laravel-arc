<?php

namespace Grazulex\Arc\Examples;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;

/**
 * Example DTO showcasing advanced features like dates and nested properties.
 */
class AddressDTO extends LaravelArcDTO
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
        // Custom validation can be added here
    }
}

class CompanyDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true, validation: 'max:255')]
    public string $name;

    #[Property(type: 'string', required: false, validation: 'email')]
    public ?string $email;

    #[Property('AddressDTO', dtoClass: AddressDTO::class, required: false)]
    public ?AddressDTO $address;

    protected function validate(array $data): void
    {
        // Custom validation can be added here
    }
}

class UserAdvancedDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true, validation: 'max:255')]
    public string $name;

    #[Property(type: 'string', required: true, validation: 'email')]
    public string $email;

    #[Property(type: 'date', required: false, format: 'Y-m-d', timezone: 'Europe/Brussels')]
    public ?Carbon $birthDate;

    #[Property(type: 'date', required: false, immutable: true)]
    public ?CarbonImmutable $createdAt;

    #[Property(type: 'date', required: false, format: 'Y-m-d H:i:s')]
    public ?Carbon $lastLoginAt;

    #[Property(type: 'nested', class: AddressDTO::class, required: false)]
    public ?AddressDTO $address;

    #[Property(type: 'nested', class: CompanyDTO::class, required: false)]
    public ?CompanyDTO $company;

    /**
     * @var array<string>
     */
    #[Property(type: 'array', required: false, default: [])]
    public array $permissions;

    #[Property(type: 'bool', required: false, default: true)]
    public bool $isActive;

    protected function validate(array $data): void
    {
        // Custom validation can be added here
    }
}
