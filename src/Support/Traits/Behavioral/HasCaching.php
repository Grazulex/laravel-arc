<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits\Behavioral;

/**
 * Provides caching behavior for DTOs
 */
trait HasCaching
{
    private ?string $cache_key = null;

    private ?int $cache_ttl = 3600; // 1 hour default

    private bool $cache_dirty = false;

    public function setCacheKey(string $key): void
    {
        $this->cache_key = $key;
    }

    public function getCacheKey(): ?string
    {
        return $this->cache_key ?? $this->generateCacheKey();
    }

    public function setCacheTtl(int $ttl): void
    {
        $this->cache_ttl = $ttl;
    }

    public function getCacheTtl(): int
    {
        return $this->cache_ttl ?? 3600;
    }

    public function markDirty(): void
    {
        $this->cache_dirty = true;
    }

    public function isDirty(): bool
    {
        return $this->cache_dirty;
    }

    public function clearCache(): void
    {
        if ($this->cache_key && function_exists('cache')) {
            cache()->forget($this->cache_key);
        }
        $this->cache_dirty = false;
    }

    private function generateCacheKey(): string
    {
        return 'dto_'.static::class.'_'.md5(serialize($this));
    }
}
