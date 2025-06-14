<?php

namespace Grazulex\Arc\Interfaces;

/**
 * Interface for advanced data transformers that support context.
 * This extends the functionality to allow transformers to access other field values.
 */
interface TransformerInterface
{
    /**
     * Transform the input value with optional context.
     *
     * @param mixed $value The value to transform
     * @param array<string, mixed> $context All DTO field values for cross-field transformations
     */
    public function transform(mixed $value, array $context = []): mixed;

    /**
     * Determine if the value should be transformed.
     *
     * @param mixed $value The value to check
     * @param array<string, mixed> $context All DTO field values
     */
    public function shouldTransform(mixed $value, array $context = []): bool;
}
