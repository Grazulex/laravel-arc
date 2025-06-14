<?php

namespace Grazulex\Arc\Transformers;

use Grazulex\Arc\Interfaces\TransformerInterface;

use function is_string;

/**
 * Transformer that trims whitespace from string values.
 */
class TrimTransformer implements TransformerInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function transform(mixed $value, array $context = []): mixed
    {
        if (is_string($value)) {
            return trim($value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function shouldTransform(mixed $value, array $context = []): bool
    {
        return is_string($value) && $value !== trim($value);
    }
}
