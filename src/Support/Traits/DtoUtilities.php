<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;

/**
 * Trait that provides utility functionality for DTOs.
 */
trait DtoUtilities
{
    /**
     * Get all property names of the DTO.
     *
     * @return array<string> Array of property names
     */
    public function getProperties(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        return array_map(fn ($prop) => $prop->getName(), $properties);
    }

    /**
     * Check if the DTO has a specific property.
     *
     * @param  string  $property  The property name
     * @return bool True if the property exists
     */
    public function hasProperty(string $property): bool
    {
        return in_array($property, $this->getProperties());
    }

    /**
     * Get the value of a property by name.
     *
     * @param  string  $property  The property name
     * @return mixed The property value
     *
     * @throws InvalidArgumentException If the property doesn't exist
     */
    public function getProperty(string $property): mixed
    {
        if (! $this->hasProperty($property)) {
            throw new InvalidArgumentException("Property '{$property}' does not exist on ".static::class);
        }

        return $this->{$property};
    }

    /**
     * Create a new instance with modified properties.
     *
     * @param  array  $properties  The properties to modify
     * @return static New instance with modified properties
     */
    public function with(array $properties): static
    {
        $currentData = $this->toArray();
        $newData = array_merge($currentData, $properties);

        return new static(...$newData);
    }

    /**
     * Compare this DTO with another for equality.
     *
     * @param  static  $other  The other DTO to compare with
     * @return bool True if the DTOs are equal
     */
    public function equals(self $other): bool
    {
        return $this->toArray() === $other->toArray();
    }
}
