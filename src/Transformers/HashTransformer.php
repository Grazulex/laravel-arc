<?php

namespace Grazulex\Arc\Transformers;

use Grazulex\Arc\Contracts\TransformerInterface;

use function is_string;

/**
 * Transformer that hashes string values (useful for passwords).
 */
class HashTransformer implements TransformerInterface
{
    public function __construct(
        private string $algorithm = 'sha256',
    ) {}

    public function transform(mixed $value): mixed
    {
        if (is_string($value)) {
            return hash($this->algorithm, $value);
        }

        return $value;
    }
}
