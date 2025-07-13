<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits\Behavioral;

use Illuminate\Support\Str;

/**
 * Trait that adds UUID functionality to DTOs.
 *
 * Adds a uuid field and related methods.
 */
trait HasUuid
{
    /**
     * Generate a new UUID for the DTO.
     */
    public function generateUuid(): static
    {
        return $this->with(['id' => (string) Str::uuid()]);
    }

    /**
     * Set a specific UUID.
     */
    public function setUuid(string $uuid): static
    {
        return $this->with(['id' => $uuid]);
    }

    /**
     * Check if the DTO has a UUID.
     */
    public function hasUuid(): bool
    {
        return isset($this->id) && $this->id !== null && $this->id !== '';
    }

    /**
     * Get the UUID or generate one if it doesn't exist.
     */
    public function getOrGenerateUuid(): string
    {
        if (! $this->hasUuid()) {
            return (string) Str::uuid();
        }

        return $this->id;
    }

    /**
     * Check if the UUID is valid format.
     */
    public function hasValidUuid(): bool
    {
        if (! $this->hasUuid()) {
            return false;
        }

        return Str::isUuid($this->id);
    }
}
