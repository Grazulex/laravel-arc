<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits\Behavioral;

use Grazulex\LaravelArc\Contracts\BehavioralDtoTrait;
use InvalidArgumentException;

/**
 * Registry for managing behavioral DTO traits.
 */
final class BehavioralTraitRegistry
{
    /**
     * @var array<string, class-string<BehavioralDtoTrait>>
     */
    private static array $traits = [];

    /**
     * Register default traits.
     */
    public static function registerDefaults(): void
    {
        self::register('HasSoftDeletes', HasSoftDeletesInfo::class);
        self::register('HasTimestamps', HasTimestampsInfo::class);
        self::register('HasUuid', HasUuidInfo::class);
        self::register('HasVersioning', HasVersioningInfo::class);
        self::register('HasTagging', HasTaggingInfo::class);
        self::register('HasAuditing', HasAuditingInfo::class);
        self::register('HasCaching', HasCachingInfo::class);
    }

    /**
     * Register a behavioral trait.
     *
     * @param  string  $name  The trait name (e.g., 'HasSoftDeletes')
     * @param  class-string<BehavioralDtoTrait>  $infoClass  The info class
     */
    public static function register(string $name, string $infoClass): void
    {
        // Verify class exists and implements the interface
        if (! class_exists($infoClass) || ! in_array(BehavioralDtoTrait::class, class_implements($infoClass) ?: [])) {
            throw new InvalidArgumentException(
                "Class {$infoClass} must implement ".BehavioralDtoTrait::class
            );
        }

        self::$traits[$name] = $infoClass;
    }

    /**
     * Get all registered traits.
     *
     * @return array<string, class-string<BehavioralDtoTrait>>
     */
    public static function getTraits(): array
    {
        if (self::$traits === []) {
            self::registerDefaults();
        }

        return self::$traits;
    }

    /**
     * Get trait info by name.
     *
     * @param  string  $name  The trait name
     * @return class-string<BehavioralDtoTrait> The trait info class
     *
     * @throws InvalidArgumentException If trait is not found
     */
    public static function getTraitInfo(string $name): string
    {
        $traits = self::getTraits();

        if (! isset($traits[$name])) {
            throw new InvalidArgumentException("Behavioral trait '{$name}' not found");
        }

        return $traits[$name];
    }

    /**
     * Check if a trait is registered.
     */
    public static function hasTrail(string $name): bool
    {
        $traits = self::getTraits();

        return isset($traits[$name]);
    }

    /**
     * Get all available trait names.
     *
     * @return array<string>
     */
    public static function getAvailableTraits(): array
    {
        return array_keys(self::getTraits());
    }

    /**
     * Get fields for multiple traits.
     *
     * @param  array<string>  $traitNames
     * @return array<string, array{type: string, required?: bool, default?: mixed}>
     */
    public static function getFieldsForTraits(array $traitNames): array
    {
        $fields = [];

        foreach ($traitNames as $traitName) {
            $traitInfoClass = self::getTraitInfo($traitName);
            $traitFields = $traitInfoClass::getTraitFields();
            $fields = array_merge($fields, $traitFields);
        }

        return $fields;
    }

    /**
     * Get validation rules for multiple traits.
     *
     * @param  array<string>  $traitNames
     * @return array<string, array<string>>
     */
    public static function getValidationRulesForTraits(array $traitNames): array
    {
        $rules = [];

        foreach ($traitNames as $traitName) {
            $traitInfoClass = self::getTraitInfo($traitName);
            $traitRules = $traitInfoClass::getTraitValidationRules();
            $rules = array_merge($rules, $traitRules);
        }

        return $rules;
    }

    /**
     * Get use statements for multiple traits.
     *
     * @param  array<string>  $traitNames
     * @return array<string>
     */
    public static function getUseStatementsForTraits(array $traitNames): array
    {
        $useStatements = [];

        foreach ($traitNames as $traitName) {
            $traitInfoClass = self::getTraitInfo($traitName);
            $traitUses = $traitInfoClass::getTraitUseStatements();
            $useStatements = array_merge($useStatements, $traitUses);
        }

        return array_unique($useStatements);
    }

    /**
     * Resolve trait name (alias for hasTrail with fixed typo).
     */
    public static function resolveTrait(string $name): bool
    {
        return self::hasTrail($name);
    }

    /**
     * Expand fields based on traits.
     *
     * @param  array<string, mixed>  $fields
     * @param  array<string>  $traitNames
     * @return array<string, mixed>
     */
    public static function expandFields(array $fields, array $traitNames): array
    {
        $expandedFields = $fields;
        $traitFields = self::getFieldsForTraits($traitNames);

        // Merge trait fields with existing fields, giving priority to explicit fields
        foreach ($traitFields as $fieldName => $fieldConfig) {
            if (! isset($expandedFields[$fieldName])) {
                $expandedFields[$fieldName] = $fieldConfig;
            }
        }

        return $expandedFields;
    }
}
