<?php

namespace Grazulex\Arc\Transformers;

use Grazulex\Arc\Contracts\TransformerInterface;

use function is_string;

/**
 * Transformer that converts string values to uppercase.
 */
class UppercaseTransformer implements TransformerInterface
{
    public function transform(mixed $value): mixed
    {
        if (is_string($value)) {
            return strtoupper($value);
        }

        return $value;
    }
}
