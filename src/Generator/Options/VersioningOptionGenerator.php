<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Options;

use Grazulex\LaravelArc\Contracts\FieldExpandingOptionGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;

final class VersioningOptionGenerator implements FieldExpandingOptionGenerator
{
    public function generate(string $name, mixed $value, DtoGenerationContext $context): string
    {
        if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return '';
        }

        return <<<'PHP'
    /**
     * Create a new version of this DTO
     */
    public function nextVersion(): static
    {
        return new static(
            ...get_object_vars($this),
            version: $this->version + 1
        );
    }

    /**
     * Check if this DTO is newer than another
     */
    public function isNewerThan(self $other): bool
    {
        return $this->version > $other->version;
    }

    /**
     * Get version information
     */
    public function getVersionInfo(): array
    {
        return [
            'version' => $this->version,
            'is_latest' => true, // Could be determined by business logic
        ];
    }
PHP;
    }

    public function expandFields(mixed $value): array
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return [
                'version' => [
                    'type' => 'integer',
                    'required' => true,
                    'default' => 1,
                    'rules' => ['integer', 'min:1'],
                ],
            ];
        }

        return [];
    }
}
