<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Options;

use Grazulex\LaravelArc\Contracts\FieldExpandingOptionGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;

final class TaggableOptionGenerator implements FieldExpandingOptionGenerator
{
    public function generate(string $name, mixed $value, DtoGenerationContext $context): string
    {
        if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return '';
        }

        return <<<'PHP'
    /**
     * Add a tag to this DTO
     */
    public function addTag(string $tag): static
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags, true)) {
            $tags[] = $tag;
        }

        return new static(
            ...get_object_vars($this),
            tags: $tags
        );
    }

    /**
     * Remove a tag from this DTO
     */
    public function removeTag(string $tag): static
    {
        $tags = $this->tags ?? [];
        $tags = array_values(array_filter($tags, fn($t) => $t !== $tag));

        return new static(
            ...get_object_vars($this),
            tags: $tags
        );
    }

    /**
     * Check if DTO has a specific tag
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? [], true);
    }

    /**
     * Get all tags
     */
    public function getTags(): array
    {
        return $this->tags ?? [];
    }

    /**
     * Filter DTOs by tag (static helper)
     */
    public static function withTag(array $dtos, string $tag): array
    {
        return array_filter($dtos, fn($dto) => $dto->hasTag($tag));
    }
PHP;
    }

    public function expandFields(mixed $value): array
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return [
                'tags' => [
                    'type' => 'array',
                    'required' => false,
                    'default' => [],
                    'rules' => ['array'],
                ],
            ];
        }

        return [];
    }
}
