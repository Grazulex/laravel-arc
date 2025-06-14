<?php

namespace Grazulex\Arc\Casting\Casters;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Casting\BaseCaster;

use function is_int;
use function is_numeric;

/**
 * Handles casting values to and from integers.
 */
class IntegerCaster extends BaseCaster
{
    protected function getSupportedCastTypes(): array
    {
        return ['int', 'integer'];
    }

    protected function performCast(mixed $value, Property $attribute): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return (int) $value;
    }

    protected function performSerialization(mixed $value, Property $attribute): int
    {
        return (int) $value;
    }
}
