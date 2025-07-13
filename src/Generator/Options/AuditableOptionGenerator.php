<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Options;

use Grazulex\LaravelArc\Contracts\FieldExpandingOptionGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;

final class AuditableOptionGenerator implements FieldExpandingOptionGenerator
{
    public function generate(string $name, mixed $value, DtoGenerationContext $context): string
    {
        if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return '';
        }

        return <<<'PHP'
    /**
     * Create audit trail entry
     */
    public function createAuditTrail(string $action, ?string $userId = null): array
    {
        return [
            'action' => $action,
            'user_id' => $userId ?? $this->updated_by ?? $this->created_by,
            'timestamp' => now(),
            'changes' => get_object_vars($this),
        ];
    }

    /**
     * Set creator information
     */
    public function setCreator(string $userId): static
    {
        return new static(
            ...get_object_vars($this),
            created_by: $userId
        );
    }

    /**
     * Set updater information
     */
    public function setUpdater(string $userId): static
    {
        return new static(
            ...get_object_vars($this),
            updated_by: $userId
        );
    }

    /**
     * Get audit information
     */
    public function getAuditInfo(): array
    {
        return [
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null,
        ];
    }
PHP;
    }

    public function expandFields(mixed $value): array
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return [
                'created_by' => [
                    'type' => 'uuid',
                    'required' => false,
                    'rules' => ['uuid', 'nullable'],
                ],
                'updated_by' => [
                    'type' => 'uuid',
                    'required' => false,
                    'rules' => ['uuid', 'nullable'],
                ],
            ];
        }

        return [];
    }
}
