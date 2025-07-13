<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits\Behavioral;

use Grazulex\LaravelArc\Contracts\BehavioralDtoTrait;

/**
 * Info class for HasTagging trait
 */
final class HasTaggingInfo implements BehavioralDtoTrait
{
    /**
     * Get the fields that this trait adds to the DTO.
     */
    public static function getTraitFields(): array
    {
        return [
            'tags' => [
                'type' => 'array',
                'required' => false,
                'default' => [],
            ],
        ];
    }

    /**
     * Get the use statements that this trait requires.
     */
    public static function getTraitUseStatements(): array
    {
        return [];
    }

    /**
     * Get additional validation rules for the trait fields.
     */
    public static function getTraitValidationRules(): array
    {
        return [
            'tags' => ['array'],
            'tags.*' => ['string', 'max:255'],
        ];
    }

    /**
     * Get the trait name for inclusion in generated DTOs.
     */
    public static function getTraitName(): string
    {
        return 'HasTagging';
    }

    /**
     * Get the methods that this trait provides for inclusion in generated DTOs.
     */
    public static function getTraitMethods(): array
    {
        return [
            '    public function addTag(string $tag): static
    {
        $currentTags = $this->tags ?? [];
        if (!in_array($tag, $currentTags)) {
            $currentTags[] = $tag;
        }
        return $this->with([\'tags\' => $currentTags]);
    }',
            '    public function removeTag(string $tag): static
    {
        $currentTags = $this->tags ?? [];
        $currentTags = array_values(array_filter($currentTags, fn($t) => $t !== $tag));
        return $this->with([\'tags\' => $currentTags]);
    }',
            '    public function getTags(): array
    {
        return $this->tags ?? [];
    }',
            '    public function hasTag(string $tag): bool
    {
        $currentTags = $this->tags ?? [];
        return in_array($tag, $currentTags);
    }',
            '    public function clearTags(): static
    {
        return $this->with([\'tags\' => []]);
    }',
        ];
    }
}
