<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits\Behavioral;

use Carbon\Carbon;

/**
 * Trait that adds timestamp functionality to DTOs.
 *
 * Adds created_at and updated_at fields and related methods.
 */
trait HasTimestamps
{
    public readonly ?Carbon $created_at;

    public readonly ?Carbon $updated_at;

    /**
     * Touch the updated_at timestamp.
     */
    public function touch(): static
    {
        return $this->with(['updated_at' => Carbon::now()]);
    }

    /**
     * Set creation timestamps.
     */
    public function setCreatedTimestamp(): static
    {
        $now = Carbon::now();

        return $this->with([
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Check if the DTO has been created.
     */
    public function isCreated(): bool
    {
        return isset($this->created_at) && $this->created_at !== null;
    }

    /**
     * Check if the DTO has been updated since creation.
     */
    public function wasRecentlyUpdated(): bool
    {
        if (! isset($this->created_at) || ! isset($this->updated_at) ||
            $this->created_at === null || $this->updated_at === null) {
            return false;
        }

        return $this->updated_at->greaterThan($this->created_at);
    }

    /**
     * Get the age of the DTO in seconds.
     */
    public function getAgeInSeconds(): ?int
    {
        if (! isset($this->created_at) || $this->created_at === null) {
            return null;
        }

        return (int) Carbon::now()->diffInSeconds($this->created_at);
    }
}
