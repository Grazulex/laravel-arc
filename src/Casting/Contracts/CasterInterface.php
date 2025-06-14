<?php

namespace Grazulex\Arc\Casting\Contracts;

use Grazulex\Arc\Attributes\Property;

/**
 * Interface for type casters in the DTO system.
 */
interface CasterInterface
{
    /**
     * Determine if this caster can handle the given cast type.
     */
    public function canCast(string $castType): bool;

    /**
     * Cast the value from input to the desired type.
     */
    public function cast(mixed $value, Property $attribute): mixed;

    /**
     * Serialize the value back to a primitive type for output.
     */
    public function serialize(mixed $value, Property $attribute): mixed;
}
