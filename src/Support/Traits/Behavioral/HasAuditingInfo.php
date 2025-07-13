<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits\Behavioral;

use Grazulex\LaravelArc\Contracts\BehavioralDtoTrait;

/**
 * Info class for HasAuditing trait
 */
final class HasAuditingInfo implements BehavioralDtoTrait
{
    public static function getTraitFields(): array
    {
        return [
            'created_by' => [
                'type' => 'string',
                'required' => false,
                'description' => 'User who created this entity',
            ],
            'updated_by' => [
                'type' => 'string',
                'required' => false,
                'description' => 'User who last updated this entity',
            ],
            'audit_trail' => [
                'type' => 'string',
                'required' => false,
                'description' => 'JSON audit trail of changes',
            ],
        ];
    }

    public static function getTraitUseStatements(): array
    {
        return [
            HasAuditing::class,
        ];
    }

    public static function getTraitValidationRules(): array
    {
        return [
            'created_by' => ['nullable', 'string'],
            'updated_by' => ['nullable', 'string'],
            'audit_trail' => ['nullable', 'string'],
        ];
    }

    public static function getTraitName(): string
    {
        return 'HasAuditing';
    }

    /**
     * Get the methods that this trait provides for inclusion in generated DTOs.
     */
    public static function getTraitMethods(): array
    {
        return [
            '    public function setCreatedBy(string $userId): static
    {
        return $this->with([\'created_by\' => $userId]);
    }',
            '    public function setUpdatedBy(string $userId): static
    {
        return $this->with([\'updated_by\' => $userId]);
    }',
            '    public function addAuditEntry(string $action, array $changes = []): static
    {
        $currentTrail = json_decode($this->audit_trail ?? \'[]\', true);
        $currentTrail[] = [
            \'action\' => $action,
            \'changes\' => $changes,
            \'timestamp\' => now()->toISOString(),
            \'user\' => $this->updated_by,
        ];
        return $this->with([\'audit_trail\' => json_encode($currentTrail)]);
    }',
        ];
    }
}
