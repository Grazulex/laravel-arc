<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Contracts;

/**
 * Contract for behavioral DTO traits that add fields and related functionality.
 *
 * This is different from functional traits (like ConvertsData, ValidatesData)
 * which only add methods without fields.
 */
interface BehavioralDtoTrait
{
    /**
     * Get the fields that this trait adds to the DTO.
     *
     * @return array<string, array{type: string, required?: bool, default?: mixed}>
     */
    public static function getTraitFields(): array;

    /**
     * Get the use statements that this trait requires.
     *
     * @return array<string>
     */
    public static function getTraitUseStatements(): array;

    /**
     * Get additional validation rules for the trait fields.
     *
     * @return array<string, array<string>>
     */
    public static function getTraitValidationRules(): array;

    /**
     * Get the trait name for inclusion in generated DTOs.
     */
    public static function getTraitName(): string;
}
