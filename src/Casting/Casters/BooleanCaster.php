<?php

namespace Grazulex\Arc\Casting\Casters;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Casting\BaseCaster;

use function in_array;
use function is_bool;
use function is_string;

/**
 * Handles casting values to and from booleans.
 */
class BooleanCaster extends BaseCaster
{
    protected function getSupportedCastTypes(): array
    {
        return ['bool', 'boolean'];
    }

    protected function performCast(mixed $value, Property $attribute): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        // Handle common string representations
        if (is_string($value)) {
            $lower = strtolower($value);
            if (in_array($lower, ['true', '1', 'yes', 'on'], true)) {
                return true;
            }
            if (in_array($lower, ['false', '0', 'no', 'off', ''], true)) {
                return false;
            }
        }

        // Handle numeric values
        if (is_numeric($value)) {
            return (bool) $value;
        }

        return (bool) $value;
    }

    protected function performSerialization(mixed $value, Property $attribute): bool
    {
        return (bool) $value;
    }
}
