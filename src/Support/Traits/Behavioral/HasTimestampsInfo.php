<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits\Behavioral;

use Grazulex\LaravelArc\Contracts\BehavioralDtoTrait;

/**
 * Helper class that provides metadata for the HasTimestamps trait.
 */
final class HasTimestampsInfo implements BehavioralDtoTrait
{
    /**
     * Get the fields that this trait adds to the DTO.
     */
    public static function getTraitFields(): array
    {
        return [
            'created_at' => [
                'type' => 'datetime',
                'required' => false,
                'default' => null,
            ],
            'updated_at' => [
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
            'created_at' => ['nullable', 'date'],
            'updated_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Get the trait name for inclusion in generated DTOs.
     */
    public static function getTraitName(): string
    {
        return 'HasTimestamps';
    }

    /**
     * Get the methods that this trait provides for inclusion in generated DTOs.
     */
    public static function getTraitMethods(): array
    {
        return [
            '    public function touch(): static
    {
        $now = \\Carbon\\Carbon::now();
        return $this->with([\'updated_at\' => $now]);
    }',
            '    public function wasRecentlyCreated(): bool
    {
        if (!$this->created_at) {
            return false;
        }
        return $this->created_at->diffInMinutes() < 1;
    }',
            '    public function getAge(): \\Carbon\\CarbonInterval
    {
        if (!$this->created_at) {
            return \\Carbon\\CarbonInterval::seconds(0);
        }
        return $this->created_at->diffAsCarbonInterval();
    }',
        ];
    }

    /**
     * Get the fully qualified trait name.
     */
    public static function getTraitClass(): string
    {
        return HasTimestamps::class;
    }
}
