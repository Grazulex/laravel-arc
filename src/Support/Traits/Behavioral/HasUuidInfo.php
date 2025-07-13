<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits\Behavioral;

use Grazulex\LaravelArc\Contracts\BehavioralDtoTrait;

/**
 * Helper class that provides metadata for the HasUuid trait.
 */
final class HasUuidInfo implements BehavioralDtoTrait
{
    /**
     * Get the fields that this trait adds to the DTO.
     */
    public static function getTraitFields(): array
    {
        return [
            'id' => [
                'type' => 'uuid',
                'required' => true,
            ],
        ];
    }

    /**
     * Get the use statements that this trait requires.
     */
    public static function getTraitUseStatements(): array
    {
        return [
            'Illuminate\Support\Str',
        ];
    }

    /**
     * Get additional validation rules for the trait fields.
     */
    public static function getTraitValidationRules(): array
    {
        return [
            'id' => ['uuid', 'required'],
        ];
    }

    /**
     * Get the trait name for inclusion in generated DTOs.
     */
    public static function getTraitName(): string
    {
        return 'HasUuid';
    }

    /**
     * Get the fully qualified trait name.
     */
    public static function getTraitClass(): string
    {
        return HasUuid::class;
    }
}
