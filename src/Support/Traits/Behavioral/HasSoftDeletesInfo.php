<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits\Behavioral;

use Grazulex\LaravelArc\Contracts\BehavioralDtoTrait;

/**
 * Helper class that provides metadata for the HasSoftDeletes trait.
 */
final class HasSoftDeletesInfo implements BehavioralDtoTrait
{
    /**
     * Get the fields that this trait adds to the DTO.
     */
    public static function getTraitFields(): array
    {
        return [
            'deleted_at' => [
                'type' => 'datetime',
                'required' => false,
                'default' => null,
            ],
        ];
    }

    /**
     * Get the use statements that this trait requires.
     */
    public static function getTraitUseStatements(): array
    {
        return [
            'Carbon\Carbon',
        ];
    }

    /**
     * Get additional validation rules for the trait fields.
     */
    public static function getTraitValidationRules(): array
    {
        return [
            'deleted_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Get the trait name for inclusion in generated DTOs.
     */
    public static function getTraitName(): string
    {
        return 'HasSoftDeletes';
    }

    /**
     * Get the methods that this trait provides for inclusion in generated DTOs.
     */
    public static function getTraitMethods(): array
    {
        return [
            '    public function softDelete(): static
    {
        return $this->with([\'deleted_at\' => \\Carbon\\Carbon::now()]);
    }',
            '    public function restore(): static
    {
        return $this->with([\'deleted_at\' => null]);
    }',
            '    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }',
            '    public function wasDeleted(): bool
    {
        return $this->deleted_at !== null;
    }',
        ];
    }

    /**
     * Get the fully qualified trait name.
     */
    public static function getTraitClass(): string
    {
        return HasSoftDeletes::class;
    }
}
