<?php

namespace Grazulex\Arc\Examples;

use Carbon\Carbon;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;

/**
 * Example DTO demonstrating smart validation rules generation.
 *
 * This would be generated with:
 * php artisan make:dto ValidationExample --with-validation --validation-strict
 */
class ValidationExampleDTO extends LaravelArcDTO
{
    // Basic string with smart validation
    #[Property(type: 'string', required: true, validation: 'required|email|max:254')]
    public string $email;

    // Password with security rules
    #[Property(type: 'string', required: true, validation: 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/')]
    public string $password;

    // Name with character restrictions
    #[Property(type: 'string', required: true, validation: 'required|min:2|max:50|regex:/^[a-zA-Z\s\-\']+$/')]
    public string $first_name;

    #[Property(type: 'string', required: true, validation: 'required|min:2|max:50|regex:/^[a-zA-Z\s\-\']+$/')]
    public string $last_name;

    // Age with reasonable limits
    #[Property(type: 'int', required: true, validation: 'required|integer|min:0|max:150')]
    public int $age;

    // Phone with format validation
    #[Property(type: 'string', required: false, validation: 'nullable|regex:/^[+]?[0-9\s\-\(\)]{7,20}$/')]
    public ?string $phone;

    // URL with proper validation
    #[Property(type: 'string', required: false, validation: 'nullable|url|max:2048')]
    public ?string $website;

    // Date with past validation (birth date)
    #[Property(type: 'date', required: false, validation: 'nullable|date|before:today')]
    public ?Carbon $birth_date;

    // Boolean with default
    #[Property(type: 'bool', required: true, default: false)]
    public bool $is_active;

    // Country code with ISO validation
    #[Property(type: 'string', required: false, validation: 'nullable|size:2|alpha')]
    public ?string $country_code;

    // Status with predefined values
    #[Property(type: 'string', required: true, validation: 'required|in:active,inactive,pending,approved,rejected')]
    public string $status;

    // UUID validation
    #[Property(type: 'string', required: false, validation: 'nullable|uuid')]
    public ?string $external_uuid;

    // Price with non-negative validation
    #[Property(type: 'float', required: true, validation: 'required|numeric|min:0')]
    public float $monthly_salary;

    // Postal code validation
    #[Property(type: 'string', required: false, validation: 'nullable|regex:/^[A-Za-z0-9\s\-]{3,10}$/')]
    public ?string $postal_code;

    // Language code validation
    #[Property(type: 'string', required: false, validation: 'nullable|regex:/^[a-z]{2}([_\-][A-Z]{2})?$/')]
    public ?string $language;
}
