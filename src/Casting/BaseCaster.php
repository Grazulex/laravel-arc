<?php

namespace Grazulex\Arc\Casting;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Casting\Contracts\CasterInterface;

use function in_array;

/**
 * Base implementation for casters with common functionality.
 */
abstract class BaseCaster implements CasterInterface
{
    public function canCast(string $castType): bool
    {
        return in_array($castType, $this->getSupportedCastTypes(), true);
    }

    public function cast(mixed $value, Property $attribute): mixed
    {
        return $this->handleNull($value, $attribute);
    }

    public function serialize(mixed $value, Property $attribute): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->performSerialization($value, $attribute);
    }

    /**
     * List of cast types this caster can handle.
     *
     * @return array<string>
     */
    abstract protected function getSupportedCastTypes(): array;

    /**
     * Handle null values consistently.
     */
    protected function handleNull(mixed $value, Property $attribute): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->performCast($value, $attribute);
    }

    /**
     * Perform the actual casting logic (to be implemented by subclasses).
     */
    abstract protected function performCast(mixed $value, Property $attribute): mixed;

    /**
     * Perform the actual serialization logic (to be implemented by subclasses).
     */
    abstract protected function performSerialization(mixed $value, Property $attribute): mixed;
}
