<?php

namespace Grazulex\Arc\Transformers;

use Grazulex\Arc\Contracts\TransformerInterface as LegacyTransformerInterface;
use Grazulex\Arc\Interfaces\TransformerInterface as NewTransformerInterface;

/**
 * Adapter to make legacy transformers work with the new context-aware interface.
 */
class LegacyTransformerAdapter implements NewTransformerInterface
{
    public function __construct(
        private LegacyTransformerInterface $legacyTransformer,
    ) {}

    /**
     * @param array<string, mixed> $context
     */
    public function transform(mixed $value, array $context = []): mixed
    {
        return $this->legacyTransformer->transform($value);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function shouldTransform(mixed $value, array $context = []): bool
    {
        // Default behavior for legacy transformers
        return $value !== null && $value !== '';
    }
}
