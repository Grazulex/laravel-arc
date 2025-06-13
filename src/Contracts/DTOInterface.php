<?php

namespace Grazulex\Arc\Contracts;

interface DTOInterface
{
    /**
     * Create a new DTO instance from an array of data.
     */
    public static function fromArray(array $data): static;

    /**
     * Convert the DTO to an array.
     */
    public function toArray(): array;

    /**
     * Get a specific attribute from the DTO.
     */
    public function get(string $key): mixed;

    /**
     * Check if the DTO has a specific attribute.
     */
    public function has(string $key): bool;
}

