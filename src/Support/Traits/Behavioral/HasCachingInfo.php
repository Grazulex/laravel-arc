<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits\Behavioral;

use Grazulex\LaravelArc\Contracts\BehavioralDtoTrait;

/**
 * Info class for HasCaching trait
 */
final class HasCachingInfo implements BehavioralDtoTrait
{
    public static function getTraitFields(): array
    {
        return [
            'cache_key' => [
                'type' => 'string',
                'required' => false,
                'description' => 'Cache key for this entity',
            ],
            'cache_ttl' => [
                'type' => 'integer',
                'required' => false,
                'description' => 'Cache TTL in seconds',
                'default' => 3600,
            ],
            'cache_dirty' => [
                'type' => 'boolean',
                'required' => false,
                'description' => 'Whether cache needs to be refreshed',
                'default' => false,
            ],
        ];
    }

    public static function getTraitUseStatements(): array
    {
        return [
            HasCaching::class,
        ];
    }

    public static function getTraitValidationRules(): array
    {
        return [
            'cache_key' => ['nullable', 'string'],
            'cache_ttl' => ['nullable', 'integer'],
            'cache_dirty' => ['nullable', 'boolean'],
        ];
    }

    public static function getTraitName(): string
    {
        return 'HasCaching';
    }

    /**
     * Get the methods that this trait provides for inclusion in generated DTOs.
     */
    public static function getTraitMethods(): array
    {
        return [
            '    public function generateCacheKey(): static
    {
        $key = \'dto_\' . strtolower(class_basename(static::class)) . \'_\' . md5(serialize($this->toArray()));
        return $this->with([\'cache_key\' => $key]);
    }',
            '    public function setCacheTtl(int $seconds): static
    {
        return $this->with([\'cache_ttl\' => $seconds]);
    }',
            '    public function markCacheDirty(): static
    {
        return $this->with([\'cache_dirty\' => true]);
    }',
            '    public function markCacheClean(): static
    {
        return $this->with([\'cache_dirty\' => false]);
    }',
        ];
    }
}
