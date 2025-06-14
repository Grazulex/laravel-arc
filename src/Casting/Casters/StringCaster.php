<?php

namespace Grazulex\Arc\Casting\Casters;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Casting\BaseCaster;

use function is_string;

/**
 * Handles casting values to and from strings.
 */
class StringCaster extends BaseCaster
{
    protected function getSupportedCastTypes(): array
    {
        return ['string'];
    }

    protected function performCast(mixed $value, Property $attribute): string
    {
        if (is_string($value)) {
            return $value;
        }

        return (string) $value;
    }

    protected function performSerialization(mixed $value, Property $attribute): string
    {
        return (string) $value;
    }
}
