<?php

namespace Grazulex\Arc\Contracts;

/**
 * Interface for data transformers that can modify values before casting.
 */
interface TransformerInterface
{
    /**
     * Transform the input value.
     */
    public function transform(mixed $value): mixed;
}
