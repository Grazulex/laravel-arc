<?php

namespace Grazulex\Arc\Transformers;

use Grazulex\Arc\Contracts\TransformerInterface;

use function is_string;

/**
 * Transformer that converts string values to lowercase.
 */
class LowercaseTransformer implements TransformerInterface
{
    public function transform(mixed $value): mixed
    {
        if (is_string($value)) {
            return strtolower($value);
        }

        return $value;
    }
}
