<?php

namespace Grazulex\Arc\Casting;

use BackedEnum;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeZone;
use Exception;
use Grazulex\Arc\Attributes\DateProperty;
use Grazulex\Arc\Attributes\NestedProperty;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Contracts\DTOInterface;
use Grazulex\Arc\Exceptions\InvalidDTOException;
use InvalidArgumentException;

use function is_array;
use function is_int;
use function is_string;

use UnitEnum;

class CastManager
{
    /**
     * Cast a value based on the property attribute.
     */
    public static function cast(mixed $value, Property $attribute): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($attribute->cast) {
            'date' => self::castToDate($value, $attribute),
            'nested' => self::castToNested($value, $attribute),
            'enum' => self::castToEnum($value, $attribute),
            default => $value
        };
    }

    /**
     * Cast value for serialization (reverse casting).
     */
    public static function serialize(mixed $value, Property $attribute): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($attribute->cast) {
            'date' => self::serializeDate($value, $attribute),
            'nested' => self::serializeNested($value, $attribute),
            'enum' => self::serializeEnum($value, $attribute),
            default => $value
        };
    }

    /**
     * Cast value to Carbon date.
     */
    private static function castToDate(mixed $value, Property $attribute): Carbon|CarbonImmutable
    {
        if ($value instanceof Carbon || $value instanceof CarbonImmutable) {
            return $value;
        }

        try {
            $dateFormat = $attribute->dateFormat ?? 'Y-m-d H:i:s';
            $timezone = null;

            if ($attribute instanceof DateProperty && $attribute->timezone) {
                $timezone = new DateTimeZone($attribute->timezone);
            }

            // Try to parse the date
            if (is_string($value)) {
                // If it's a specific format, parse it
                if ($dateFormat !== 'Y-m-d H:i:s') {
                    $date = $attribute instanceof DateProperty && $attribute->immutable
                        ? CarbonImmutable::createFromFormat($dateFormat, $value, $timezone)
                        : Carbon::createFromFormat($dateFormat, $value, $timezone);
                } else {
                    // Let Carbon parse it automatically
                    $date = $attribute instanceof DateProperty && $attribute->immutable
                        ? CarbonImmutable::parse($value, $timezone)
                        : Carbon::parse($value, $timezone);
                }
            } elseif (is_int($value)) {
                // Unix timestamp
                $date = $attribute instanceof DateProperty && $attribute->immutable
                    ? CarbonImmutable::createFromTimestamp($value, $timezone)
                    : Carbon::createFromTimestamp($value, $timezone);
            } else {
                throw new InvalidArgumentException('Invalid date format');
            }

            if (!$date) {
                throw new InvalidArgumentException('Could not parse date');
            }

            return $date;
        } catch (Exception $e) {
            throw InvalidDTOException::forCastingError('date', $value, $e->getMessage());
        }
    }

    /**
     * Cast value to nested DTO.
     *
     * @return array<DTOInterface>|DTOInterface
     */
    private static function castToNested(mixed $value, Property $attribute): array|DTOInterface
    {
        if (!$attribute->nested) {
            throw new InvalidArgumentException('Nested class not specified');
        }

        $dtoClass = $attribute->nested;

        if (!class_exists($dtoClass)) {
            throw InvalidDTOException::forCastingError('nested', $value, "Class {$dtoClass} does not exist");
        }

        if (!is_subclass_of($dtoClass, DTOInterface::class)) {
            throw InvalidDTOException::forCastingError('nested', $value, "Class {$dtoClass} must implement DTOInterface");
        }

        try {
            // Handle collections
            if ($attribute instanceof NestedProperty && $attribute->isCollection) {
                if (!is_array($value)) {
                    throw new InvalidArgumentException('Value must be an array for collection');
                }

                return array_map(function ($item) use ($dtoClass) {
                    if ($item instanceof $dtoClass) {
                        return $item;
                    }

                    return new $dtoClass(is_array($item) ? $item : []);
                }, $value);
            }

            // Handle single nested DTO
            if ($value instanceof $dtoClass) {
                return $value;
            }

            if (is_array($value)) {
                return new $dtoClass($value);
            }

            throw new InvalidArgumentException('Value must be an array or instance of ' . $dtoClass);
        } catch (Exception $e) {
            throw InvalidDTOException::forCastingError('nested', $value, $e->getMessage());
        }
    }

    /**
     * Serialize Carbon date to string.
     */
    private static function serializeDate(mixed $value, Property $attribute): string
    {
        if ($value instanceof Carbon || $value instanceof CarbonImmutable) {
            $format = $attribute->dateFormat ?? 'Y-m-d H:i:s';

            return $value->format($format);
        }

        return (string) $value;
    }

    /**
     * Serialize nested DTO to array.
     *
     * @return array<string, mixed>
     */
    private static function serializeNested(mixed $value, Property $attribute): array
    {
        if ($attribute instanceof NestedProperty && $attribute->isCollection) {
            if (!is_array($value)) {
                return [];
            }

            return array_map(function ($item) {
                if ($item instanceof DTOInterface) {
                    return $item->toArray();
                }

                return $item;
            }, $value);
        }

        if ($value instanceof DTOInterface) {
            return $value->toArray();
        }

        return is_array($value) ? $value : [];
    }

    /**
     * Cast value to PHP enum.
     */
    private static function castToEnum(mixed $value, Property $attribute): BackedEnum|UnitEnum
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
                // Try to get enum from value
                return $enumClass::from($value);
            }

            // It's a UnitEnum (no values)
            if (is_string($value)) {
                // Try to get enum from name
                foreach ($enumClass::cases() as $case) {
                    if ($case->name === $value) {
                        return $case;
                    }
                }
            }

            throw new InvalidArgumentException("Cannot convert value to enum {$enumClass}");
        } catch (Exception $e) {
            throw InvalidDTOException::forCastingError('enum', $value, $e->getMessage());
        }
    }

    /**
     * Serialize enum to its value or name.
     */
    private static function serializeEnum(mixed $value, Property $attribute): int|string
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        return (string) $value;
    }
}
