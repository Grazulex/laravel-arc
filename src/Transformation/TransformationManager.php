<?php

namespace Grazulex\Arc\Transformation;

use Grazulex\Arc\Contracts\TransformerInterface as LegacyTransformerInterface;
use Grazulex\Arc\Interfaces\TransformerInterface;
use Grazulex\Arc\Transformers\LegacyTransformerAdapter;
use InvalidArgumentException;

use function is_string;

/**
 * Manages transformation pipelines for DTO properties.
 */
class TransformationManager
{
    /**
     * Apply a chain of transformers to a value with context support.
     *
     * @param array<LegacyTransformerInterface|string|TransformerInterface> $transformers
     * @param array<string, mixed> $context
     */
    public static function transform(mixed $value, array $transformers, array $context = []): mixed
    {
        $result = $value;

        foreach ($transformers as $transformer) {
            $transformerInstance = self::resolveTransformer($transformer);

            // Check if transformer should be applied
            if ($transformerInstance->shouldTransform($result, $context)) {
                $result = $transformerInstance->transform($result, $context);
            }
        }

        return $result;
    }

    /**
     * Check if a value should be transformed (not null/empty based on transformer needs).
     *
     * @param array<LegacyTransformerInterface|string|TransformerInterface> $transformers
     * @param array<string, mixed> $context
     */
    public static function shouldTransform(mixed $value, array $transformers, array $context = []): bool
    {
        // Don't transform if no transformers specified
        if (empty($transformers)) {
            return false;
        }

        // Check if any transformer wants to process this value
        foreach ($transformers as $transformer) {
            $transformerInstance = self::resolveTransformer($transformer);
            if ($transformerInstance->shouldTransform($value, $context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve a transformer from class name or instance.
     */
    private static function resolveTransformer(LegacyTransformerInterface|string|TransformerInterface $transformer): TransformerInterface
    {
        // New interface
        if ($transformer instanceof TransformerInterface) {
            return $transformer;
        }

        // Legacy interface - wrap with adapter
        if ($transformer instanceof LegacyTransformerInterface) {
            return new LegacyTransformerAdapter($transformer);
        }

        if (is_string($transformer)) {
            if (!class_exists($transformer)) {
                throw new InvalidArgumentException("Transformer class '{$transformer}' does not exist.");
            }

            $instance = new $transformer();

            // Check for new interface first
            if ($instance instanceof TransformerInterface) {
                return $instance;
            }

            // Check for legacy interface and wrap
            if ($instance instanceof LegacyTransformerInterface) {
                return new LegacyTransformerAdapter($instance);
            }

            throw new InvalidArgumentException("Class '{$transformer}' must implement TransformerInterface.");
        }

        throw new InvalidArgumentException('Transformer must be a class name string or TransformerInterface instance.');
    }
}
