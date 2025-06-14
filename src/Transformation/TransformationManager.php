<?php

namespace Grazulex\Arc\Transformation;

use Grazulex\Arc\Contracts\TransformerInterface;
use InvalidArgumentException;

use function is_string;

/**
 * Manages transformation pipelines for DTO properties.
 */
class TransformationManager
{
    /**
     * Apply a chain of transformers to a value.
     *
     * @param array<string|TransformerInterface> $transformers
     */
    public static function transform(mixed $value, array $transformers): mixed
    {
        $result = $value;

        foreach ($transformers as $transformer) {
            $transformerInstance = self::resolveTransformer($transformer);
            $result = $transformerInstance->transform($result);
        }

        return $result;
    }

    /**
     * Check if a value should be transformed (not null/empty based on transformer needs).
     *
     * @param array<string|TransformerInterface> $transformers
     */
    public static function shouldTransform(mixed $value, array $transformers): bool
    {
        // Don't transform null values
        if ($value === null) {
            return false;
        }

        // Don't transform if no transformers specified
        if (empty($transformers)) {
            return false;
        }

        return true;
    }

    /**
     * Resolve a transformer from class name or instance.
     */
    private static function resolveTransformer(string|TransformerInterface $transformer): TransformerInterface
    {
        if ($transformer instanceof TransformerInterface) {
            return $transformer;
        }

        if (is_string($transformer)) {
            if (!class_exists($transformer)) {
                throw new InvalidArgumentException("Transformer class '{$transformer}' does not exist.");
            }

            $instance = new $transformer();

            if (!$instance instanceof TransformerInterface) {
                throw new InvalidArgumentException("Class '{$transformer}' must implement TransformerInterface.");
            }

            return $instance;
        }

        throw new InvalidArgumentException('Transformer must be a class name string or TransformerInterface instance.');
    }
}
