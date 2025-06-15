<?php

namespace Grazulex\Arc\Casting\Casters;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Exception;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Casting\BaseCaster;
use Grazulex\Arc\Exceptions\InvalidDTOException;
use InvalidArgumentException;

use function is_int;
use function is_string;

/**
 * Handles casting values to and from Carbon date instances.
 */
class DateCaster extends BaseCaster
{
    protected function getSupportedCastTypes(): array
    {
        return ['date'];
    }

    protected function performCast(mixed $value, Property $attribute): Carbon|CarbonImmutable
    {
        if ($value instanceof Carbon || $value instanceof CarbonImmutable) {
            return $value;
        }

        try {
            // Parse the date using Carbon's intelligent parsing
            if (is_string($value)) {
                $date = Carbon::parse($value);
            } elseif (is_int($value)) {
                // Unix timestamp
                $date = Carbon::createFromTimestamp($value);
            } else {
                throw new InvalidArgumentException('Invalid date format');
            }

            // Check if immutable is requested based on property type hint
            // We can detect this from the property type in the DTO
            return $date;
        } catch (Exception $e) {
            throw InvalidDTOException::forCastingError('date', $value, $e->getMessage());
        }
    }

    /**
     * Serializes the date value into a format suitable for JSON or other outputs.
     *
     * @param mixed $value the value to serialize, expected to be a Carbon instance
     * @param Property $attribute the property attribute containing serialization options
     *
     * @return array<string, int|string>|string the serialized date value
     */
    protected function performSerialization(mixed $value, Property $attribute): array|string
    {
        if ($value instanceof Carbon || $value instanceof CarbonImmutable) {
            // If we're explicitly asking for a specific format via the attribute
            if ($attribute->format) {
                return $value->format($attribute->format);
            }

            // Return multiple formats by default
            return [
                'iso' => $value->toIso8601String(),
                'diff_from_now' => $value->diffForHumans(),
                'utc' => $value->toRfc3339String(),
                'formatted' => $value->format('d/m/Y H:i:s'),
                'timestamp' => $value->timestamp,
                'timezone' => $attribute->timezone ?? 'UTC',
                'local' => $value->setTimezone($attribute->timezone ?? 'UTC')->format('d/m/Y H:i:s'),
            ];
        }

        return (string) $value;
    }
}
