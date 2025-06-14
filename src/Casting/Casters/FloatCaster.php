<?php

namespace Grazulex\Arc\Casting\Casters;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Casting\BaseCaster;

use function is_float;
use function is_numeric;

/**
 * Handles casting values to and from floats.
 */
class FloatCaster extends BaseCaster
{
    protected function getSupportedCastTypes(): array
    {
        return ['float', 'double'];
    }

    protected function performCast(mixed $value, Property $attribute): float
    {
        if (is_float($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return (float) $value;
    }

    protected function performSerialization(mixed $value, Property $attribute): float
    {
        return (float) $value;
    }
}
