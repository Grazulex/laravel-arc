<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Options;

use Grazulex\LaravelArc\Contracts\FieldExpandingOptionGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;

final class SluggableOptionGenerator implements FieldExpandingOptionGenerator
{
    public function generate(string $name, mixed $value, DtoGenerationContext $context): string
    {
        if (! $value || ! is_array($value)) {
            return '';
        }

        $sourceField = $value['from'] ?? 'name';

        return <<<PHP
    /**
     * Generate slug from {$sourceField}
     */
    public function generateSlug(): static
    {
        \$slug = \\Illuminate\\Support\\Str::slug(\$this->{$sourceField} ?? '');
        
        return new static(
            ...get_object_vars(\$this),
            slug: \$slug
        );
    }

    /**
     * Update slug when source field changes
     */
    public function updateSlug(): static
    {
        return \$this->generateSlug();
    }

    /**
     * Get URL-friendly slug
     */
    public function getSlug(): string
    {
        return \$this->slug ?? \\Illuminate\\Support\\Str::slug(\$this->{$sourceField} ?? '');
    }

    /**
     * Check if slug is unique (requires external validation)
     */
    public function hasUniqueSlug(): bool
    {
        // This would need to be implemented with actual uniqueness check
        return !empty(\$this->slug);
    }
PHP;
    }

    public function expandFields(mixed $value): array
    {
        if ($value && is_array($value)) {
            return [
                'slug' => [
                    'type' => 'string',
                    'required' => false,
                    'rules' => ['string', 'max:255', 'regex:/^[a-z0-9-]+$/'],
                ],
            ];
        }

        return [];
    }
}
