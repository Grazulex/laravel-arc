<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Options;

use Grazulex\LaravelArc\Contracts\OptionGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;

final class CacheableOptionGenerator implements OptionGenerator
{
    public function generate(string $name, mixed $value, DtoGenerationContext $context): string
    {
        if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return '';
        }

        return <<<'PHP'
    /**
     * Get cache key for this DTO
     */
    public function getCacheKey(): string
    {
        $className = static::class;
        $data = get_object_vars($this);
        $hash = hash('sha256', serialize($data));
        
        return "dto:{$className}:{$hash}";
    }

    /**
     * Cache this DTO
     */
    public function cache(int $ttl = 3600): static
    {
        cache()->put($this->getCacheKey(), $this, $ttl);
        return $this;
    }

    /**
     * Get from cache or create new
     */
    public static function fromCache(string $cacheKey): ?static
    {
        return cache()->get($cacheKey);
    }

    /**
     * Remove from cache
     */
    public function clearCache(): bool
    {
        return cache()->forget($this->getCacheKey());
    }

    /**
     * Check if this DTO is cached
     */
    public function isCached(): bool
    {
        return cache()->has($this->getCacheKey());
    }

    /**
     * Get cache metadata
     */
    public function getCacheMetadata(): array
    {
        $key = $this->getCacheKey();
        return [
            'key' => $key,
            'exists' => cache()->has($key),
            'size' => strlen(serialize($this)),
        ];
    }
PHP;
    }
}
