<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits\Behavioral;

/**
 * Provides versioning behavior for DTOs
 */
trait HasVersioning
{
    public function nextVersion(): static
    {
        return $this->with(['version' => 1]);
    }

    public function incrementVersion(): static
    {
        return $this->nextVersion();
    }

    public function getVersion(): ?int
    {
        return $this->version ?? null;
    }
}
