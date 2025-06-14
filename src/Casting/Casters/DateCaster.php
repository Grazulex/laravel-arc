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

            // Date parsing should always succeed with Carbon

            return $date;
        } catch (Exception $e) {
            throw InvalidDTOException::forCastingError('date', $value, $e->getMessage());
        }
    }

    protected function performSerialization(mixed $value, Property $attribute): string
    {
        if ($value instanceof Carbon || $value instanceof CarbonImmutable) {
            return $value->toDateTimeString();
        }

        return (string) $value;
    }
}
