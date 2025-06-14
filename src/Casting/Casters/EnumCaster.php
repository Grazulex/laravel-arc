<?php

namespace Grazulex\Arc\Casting\Casters;

use BackedEnum;
use Exception;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Casting\BaseCaster;
use Grazulex\Arc\Exceptions\InvalidDTOException;
use InvalidArgumentException;

use function is_string;

use UnitEnum;

/**
 * Handles casting values to and from PHP enums.
 */
class EnumCaster extends BaseCaster
{
    protected function getSupportedCastTypes(): array
    {
        return ['enum'];
    }

    protected function performCast(mixed $value, Property $attribute): BackedEnum|UnitEnum
    {
        if (!$attribute->nested) {
            throw new InvalidArgumentException('Enum class not specified');
        }

        $enumClass = $attribute->nested;

        if (!enum_exists($enumClass)) {
            throw InvalidDTOException::forCastingError('enum', $value, "Enum {$enumClass} does not exist");
        }

        // If already the correct enum instance
        if ($value instanceof $enumClass) {
            return $value;
        }

        try {
            // Check if it's a BackedEnum (has values)
            if (is_subclass_of($enumClass, BackedEnum::class)) {
                return $this->castToBackedEnum($value, $enumClass);
            }

            // It's a UnitEnum (no values)
            return $this->castToUnitEnum($value, $enumClass);
        } catch (Exception $e) {
            throw InvalidDTOException::forCastingError('enum', $value, $e->getMessage());
        }
    }

    protected function performSerialization(mixed $value, Property $attribute): int|string
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        return (string) $value;
    }

    /**
     * Cast value to a BackedEnum.
     *
     * @param class-string<BackedEnum> $enumClass
     */
    private function castToBackedEnum(mixed $value, string $enumClass): BackedEnum
    {
        // Try to get enum from value
        return $enumClass::from($value);
    }

    /**
     * Cast value to a UnitEnum.
     *
     * @param class-string<UnitEnum> $enumClass
     */
    private function castToUnitEnum(mixed $value, string $enumClass): UnitEnum
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Value must be a string for UnitEnum');
        }

        // Try to get enum from name
        foreach ($enumClass::cases() as $case) {
            if ($case->name === $value) {
                return $case;
            }
        }

        throw new InvalidArgumentException("Cannot convert value '{$value}' to enum {$enumClass}");
    }
}
