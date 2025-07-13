<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits\Behavioral;

/**
 * Provides tagging behavior for DTOs
 */
trait HasTagging
{
    public function addTag(string $tag): static
    {
        $currentTags = $this->tags ?? [];
        if (! in_array($tag, $currentTags)) {
            $currentTags[] = $tag;
        }

        return $this->with(['tags' => $currentTags]);
    }

    public function removeTag(string $tag): static
    {
        $currentTags = $this->tags ?? [];
        $currentTags = array_values(array_filter($currentTags, fn ($t): true => $t !== $tag));

        return $this->with(['tags' => $currentTags]);
    }

    public function getTags(): array
    {
        return $this->tags ?? [];
    }

    public function hasTag(string $tag): bool
    {
        $currentTags = $this->tags ?? [];

        return in_array($tag, $currentTags);
    }

    public function clearTags(): static
    {
        return $this->with(['tags' => []]);
    }
}
