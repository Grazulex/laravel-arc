<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits\Behavioral;

use Grazulex\LaravelArc\Contracts\BehavioralDtoTrait;

/**
 * Info class for HasVersioning trait
 */
final class HasVersioningInfo implements BehavioralDtoTrait
{
    /**
     * Get the fields that this trait adds to the DTO.
     */
    public static function getTraitFields(): array
    {
        return [
            'version' => [
                'type' => 'integer',
                'required' => true,
                'default' => 1,
            ],
        ];
    }

    /**
     * Get the use statements that this trait requires.
     */
    public static function getTraitUseStatements(): array
    {
        return [];
    }

    /**
     * Get additional validation rules for the trait fields.
     */
    public static function getTraitValidationRules(): array
    {
        return [
            'version' => ['integer', 'min:1'],
        ];
    }

    /**
     * Get the trait name for inclusion in generated DTOs.
     */
    public static function getTraitName(): string
    {
        return 'HasVersioning';
    }
}
