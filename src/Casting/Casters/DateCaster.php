<?php

namespace Grazulex\Arc\Casting\Casters;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeZone;
use Exception;
use Grazulex\Arc\Attributes\DateProperty;
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
            $dateFormat = 'Y-m-d H:i:s';
            $timezone = null;

            if ($attribute instanceof DateProperty) {
                $dateFormat = $attribute->format;
                if ($attribute->timezone) {
                    $timezone = new DateTimeZone($attribute->timezone);
                }
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

    protected function performSerialization(mixed $value, Property $attribute): string
    {
        if ($value instanceof Carbon || $value instanceof CarbonImmutable) {
            $format = 'Y-m-d H:i:s';

            if ($attribute instanceof DateProperty) {
                $format = $attribute->format;
            }

            return $value->format($format);
        }

        return (string) $value;
    }
}
