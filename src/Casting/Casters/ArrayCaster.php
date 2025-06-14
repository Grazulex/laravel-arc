<?php

namespace Grazulex\Arc\Casting\Casters;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Casting\BaseCaster;

use function is_array;
use function is_object;
use function is_string;
use function json_decode;

/**
 * Handles casting values to and from arrays.
 */
class ArrayCaster extends BaseCaster
{
    protected function getSupportedCastTypes(): array
    {
        return ['array'];
    }

    /**
     * @return array<int|string, mixed>
     */
    protected function performCast(mixed $value, Property $attribute): array
    {
        if (is_array($value)) {
            return $value;
        }

        // Try to decode JSON strings
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        // Convert objects to arrays
        if (is_object($value)) {
            return (array) $value;
        }

        // Wrap scalar values in array
        return [$value];
    }

    /**
     * @return array<int|string, mixed>
     */
    protected function performSerialization(mixed $value, Property $attribute): array
    {
        if (is_array($value)) {
            return $value;
        }

        return (array) $value;
    }
}
