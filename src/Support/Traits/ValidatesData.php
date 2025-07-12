<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

/**
 * Trait that provides validation functionality for DTOs.
 */
trait ValidatesData
{
    /**
     * Validate the given data against the DTO's validation rules.
     *
     * @param  array  $data  The data to validate
     * @return array The validated data
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function validate(array $data): array
    {
        return static::validator($data)->validate();
    }

    /**
     * Create a validator instance for the given data.
     *
     * @param  array  $data  The data to validate
     * @return Validator The validator instance
     */
    public static function validator(array $data): Validator
    {
        return ValidatorFacade::make($data, static::rules());
    }

    /**
     * Validate the given data and return whether it passes validation.
     *
     * @param  array  $data  The data to validate
     * @return bool True if validation passes, false otherwise
     */
    public static function passes(array $data): bool
    {
        return ! static::validator($data)->fails();
    }

    /**
     * Validate the given data and return whether it fails validation.
     *
     * @param  array  $data  The data to validate
     * @return bool True if validation fails, false otherwise
     */
    public static function fails(array $data): bool
    {
        return static::validator($data)->fails();
    }
}
