<?php

namespace Grazulex\Arc\Casting\Casters;

use BackedEnum;
use Exception;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Casting\BaseCaster;
use Grazulex\Arc\Exceptions\InvalidDTOException;
use InvalidArgumentException;
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

        try {
            // If value is already an instance of the enum, return it
            if ($value instanceof $enumClass) {
                return $value;
            }

            // Handle BackedEnum (enums with values)
            if (is_subclass_of($enumClass, BackedEnum::class)) {
                return $enumClass::from($value);
            }

            // Handle UnitEnum (pure enums without values)
            // Since we already checked for BackedEnum, this must be UnitEnum
            // Try direct name match first
            foreach ($enumClass::cases() as $case) {
                if ($case->name === $value) {
                    return $case;
                }
            }

            // Try case-insensitive match
            foreach ($enumClass::cases() as $case) {
                if (strtoupper($case->name) === strtoupper($value)) {
                    return $case;
                }
            }

            throw new InvalidArgumentException("Unknown enum case: {$value} for enum {$enumClass}");
        } catch (Exception $e) {
            throw InvalidDTOException::forCastingError('enum', $value, $e->getMessage());
        }
    }

    protected function performSerialization(mixed $value, Property $attribute): string
    {
        if ($value instanceof BackedEnum) {
            // BackedEnum serializes to its value
            return (string) $value->value;
        }

        if ($value instanceof UnitEnum) {
            // UnitEnum serializes to its name
            return $value->name;
        }

        return (string) $value;
    }
}
