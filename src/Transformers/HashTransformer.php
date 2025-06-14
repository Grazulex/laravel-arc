<?php

namespace Grazulex\Arc\Transformers;

use Grazulex\Arc\Interfaces\TransformerInterface;

use function is_string;

/**
 * Transformer that hashes string values (useful for passwords).
 */
class HashTransformer implements TransformerInterface
{
    public function __construct(
        private string $algorithm = 'sha256',
    ) {}

    /**
     * @param array<string, mixed> $context
     */
    public function transform(mixed $value, array $context = []): mixed
    {
        if (is_string($value)) {
            return hash($this->algorithm, $value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function shouldTransform(mixed $value, array $context = []): bool
    {
        return is_string($value) && !empty($value);
    }
}
