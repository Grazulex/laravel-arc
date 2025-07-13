<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Export;

use Grazulex\LaravelArc\Contracts\DtoCollectionExporter;
use Grazulex\LaravelArc\Contracts\DtoExporter;
use InvalidArgumentException;

/**
 * Abstract base class for DTO exporters that support both single and collection export.
 */
abstract class AbstractExporter implements DtoCollectionExporter, DtoExporter
{
    /**
     * Export a single DTO to the target format.
     */
    abstract public function export(array $data, array $options = []): string;

    /**
     * Export a collection of DTOs to the target format.
     */
    abstract public function exportCollection(array $dataCollection, array $options = []): string;

    /**
     * Get the format identifier for this exporter.
     */
    abstract public function getFormat(): string;

    /**
     * Helper method to merge default options with provided options.
     */
    protected function mergeOptions(array $defaultOptions, array $options): array
    {
        return array_merge($defaultOptions, $options);
    }

    /**
     * Helper method to validate that data is not empty.
     */
    protected function validateData(array $data, string $context = 'export'): void
    {
        if ($data === []) {
            throw new InvalidArgumentException("Cannot {$context} empty data");
        }
    }

    /**
     * Helper method to convert a collection to array format.
     */
    protected function normalizeCollection(array $dataCollection): array
    {
        if ($dataCollection === []) {
            return [];
        }

        // Ensure all items are arrays
        return array_map(function ($item): array {
            return is_array($item) ? $item : (array) $item;
        }, $dataCollection);
    }
}
