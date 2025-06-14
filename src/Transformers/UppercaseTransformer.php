<?php

namespace Grazulex\Arc\Transformers;

use Grazulex\Arc\Interfaces\TransformerInterface;

use function is_string;

/**
 * Transformer that converts string values to uppercase.
 */
class UppercaseTransformer implements TransformerInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function transform(mixed $value, array $context = []): mixed
    {
        if (is_string($value)) {
            return strtoupper($value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function shouldTransform(mixed $value, array $context = []): bool
    {
        return is_string($value) && $value !== strtoupper($value);
    }
}
