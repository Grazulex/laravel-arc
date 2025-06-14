<?php

namespace Grazulex\Arc\Casting;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Casting\Casters\ArrayCaster;
use Grazulex\Arc\Casting\Casters\BooleanCaster;
use Grazulex\Arc\Casting\Casters\DateCaster;
use Grazulex\Arc\Casting\Casters\EnumCaster;
use Grazulex\Arc\Casting\Casters\FloatCaster;
use Grazulex\Arc\Casting\Casters\IntegerCaster;
use Grazulex\Arc\Casting\Casters\NestedCaster;
use Grazulex\Arc\Casting\Casters\StringCaster;
use Grazulex\Arc\Casting\Contracts\CasterInterface;

/**
 * Manages type casting using dedicated caster classes.
 *
 * This is the refactored version of CastManager that follows
 * the Single Responsibility Principle by delegating to specific casters.
 */
class CastManager
{
    /**
     * @var array<CasterInterface>
     */
    private array $casters;

    public function __construct()
    {
        $this->initializeCasters();
    }

    /**
     * Cast a value based on the property attribute.
     */
    public static function cast(mixed $value, Property $attribute): mixed
    {
        $manager = new self();

        return $manager->performCast($value, $attribute);
    }

    /**
     * Cast value for serialization (reverse casting).
     */
    public static function serialize(mixed $value, Property $attribute): mixed
    {
        $manager = new self();

        return $manager->performSerialization($value, $attribute);
    }

    /**
     * Register a new caster.
     */
    public function registerCaster(CasterInterface $caster): void
    {
        $this->casters[] = $caster;
    }

    /**
     * Get all registered casters.
     *
     * @return array<CasterInterface>
     */
    public function getCasters(): array
    {
        return $this->casters;
    }

    /**
     * Remove all casters and replace with the given ones.
     *
     * @param array<CasterInterface> $casters
     */
    public function setCasters(array $casters): void
    {
        $this->casters = $casters;
    }

    /**
     * Initialize the available casters.
     */
    private function initializeCasters(): void
    {
        $this->casters = [
            // Basic type casters
            new StringCaster(),
            new IntegerCaster(),
            new FloatCaster(),
            new BooleanCaster(),
            new ArrayCaster(),

            // Advanced type casters
            new DateCaster(),
            new NestedCaster(),
            new EnumCaster(),
        ];
    }

    /**
     * Perform the actual casting using the appropriate caster.
     */
    private function performCast(mixed $value, Property $attribute): mixed
    {
        if ($value === null) {
            return null;
        }

        $caster = $this->findCasterForType($attribute->cast);

        if ($caster === null) {
            // No specific caster found, return value as-is
            return $value;
        }

        return $caster->cast($value, $attribute);
    }

    /**
     * Perform the actual serialization using the appropriate caster.
     */
    private function performSerialization(mixed $value, Property $attribute): mixed
    {
        if ($value === null) {
            return null;
        }

        $caster = $this->findCasterForType($attribute->cast);

        if ($caster === null) {
            // No specific caster found, return value as-is
            return $value;
        }

        return $caster->serialize($value, $attribute);
    }

    /**
     * Find the appropriate caster for the given cast type.
     */
    private function findCasterForType(string $castType): ?CasterInterface
    {
        foreach ($this->casters as $caster) {
            if ($caster->canCast($castType)) {
                return $caster;
            }
        }

        return null;
    }
}
