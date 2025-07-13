<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Export\Exporters;

use Grazulex\LaravelArc\Support\Export\AbstractExporter;

/**
 * PHP array exporter for DTOs.
 */
final class PhpArrayExporter extends AbstractExporter
{
    /**
     * Export a single DTO to PHP array format.
     */
    public function export(array $data, array $options = []): string
    {
        $defaultOptions = [
            'short_syntax' => false,
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);

        return $mergedOptions['short_syntax']
            ? $this->formatArrayShortSyntax($data)
            : var_export($data, true);
    }

    /**
     * Export a collection of DTOs to PHP array format.
     */
    public function exportCollection(array $dataCollection, array $options = []): string
    {
        $defaultOptions = [
            'short_syntax' => false,
            'wrap_in_data' => true,
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);
        $normalizedData = $this->normalizeCollection($dataCollection);

        $output = $mergedOptions['wrap_in_data']
            ? ['data' => $normalizedData]
            : $normalizedData;

        return $mergedOptions['short_syntax']
            ? $this->formatArrayShortSyntax($output)
            : var_export($output, true);
    }

    /**
     * Get the format identifier.
     */
    public function getFormat(): string
    {
        return 'php_array';
    }

    /**
     * Format array using short syntax [].
     */
    private function formatArrayShortSyntax(array $data): string
    {
        return str_replace(
            ['array (', ')'],
            ['[', ']'],
            var_export($data, true)
        );
    }
}
