<?php

namespace Grazulex\Arc\Contracts;

interface DTOFactoryInterface
{
    /**
     * Set a specific value for a property.
     */
    public function with(string $property, mixed $value): static;

    /**
     * Set multiple values at once.
     *
     * @param array<string, mixed> $attributes
     */
    public function withAttributes(array $attributes): static;

    /**
     * Generate random data for the DTO.
     */
    public function fake(): static;

    /**
     * Generate random data for specific properties.
     *
     * @param array<string> $properties
     */
    public function fakeOnly(array $properties): static;

    /**
     * Create a single DTO instance.
     */
    public function create(): DTOInterface;

    /**
     * Create multiple DTO instances.
     *
     * @param int $count
     * @return array<DTOInterface>
     */
    public function createMany(int $count): array;

    /**
     * Create a DTO instance without persisting (for testing).
     */
    public function make(): DTOInterface;

    /**
     * Create multiple DTO instances without persisting.
     *
     * @param int $count
     * @return array<DTOInterface>
     */
    public function makeMany(int $count): array;
}

