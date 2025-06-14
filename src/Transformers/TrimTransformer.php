<?php

namespace Grazulex\Arc\Transformers;

use Grazulex\Arc\Contracts\TransformerInterface;

use function is_string;

/**
 * Transformer that trims whitespace from string values.
 */
class TrimTransformer implements TransformerInterface
{
    public function transform(mixed $value): mixed
    {
        if (is_string($value)) {
            return trim($value);
        }

        return $value;
    }
}
