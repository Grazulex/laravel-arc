<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits\Behavioral;

/**
 * Provides auditing behavior for DTOs
 */
trait HasAuditing
{
    public ?string $created_by = null;

    public ?string $updated_by = null;

    public ?string $audit_trail = null;

    public function setCreatedBy(string $user): void
    {
        $this->created_by = $user;
    }

    public function setUpdatedBy(string $user): void
    {
        $this->updated_by = $user;
    }

    public function addAuditEntry(string $action, string $user): void
    {
        $entry = json_encode([
            'action' => $action,
            'user' => $user,
            'timestamp' => now()->toISOString(),
        ]);

        $trail = $this->audit_trail ? json_decode($this->audit_trail, true) : [];
        $trail[] = json_decode($entry, true);
        $this->audit_trail = json_encode($trail);
    }

    public function getAuditTrail(): array
    {
        return $this->audit_trail ? json_decode($this->audit_trail, true) : [];
    }
}
