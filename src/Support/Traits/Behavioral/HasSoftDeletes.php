<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits\Behavioral;

use Carbon\Carbon;

/**
 * Trait that adds soft delete functionality to DTOs.
 *
 * Adds a deleted_at field and related methods.
 */
trait HasSoftDeletes
{
    public readonly ?Carbon $deleted_at;

    /**
     * Mark the DTO as deleted.
     */
    public function delete(): static
    {
        return $this->with(['deleted_at' => Carbon::now()]);
    }

    /**
     * Restore the DTO (undelete).
     */
    public function restore(): static
    {
        return $this->with(['deleted_at' => null]);
    }

    /**
     * Check if the DTO is soft deleted.
     */
    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }

    /**
     * Check if the DTO is not soft deleted.
     */
    public function isNotDeleted(): bool
    {
        return $this->deleted_at === null;
    }
}
