<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Export\Exporters;

use Grazulex\LaravelArc\Support\Export\AbstractExporter;

/**
 * Query string exporter for DTOs.
 */
final class QueryStringExporter extends AbstractExporter
{
    /**
     * Export a single DTO to query string format.
     */
    public function export(array $data, array $options = []): string
    {
        $defaultOptions = [
            'encoding_type' => PHP_QUERY_RFC1738, // or PHP_QUERY_RFC3986
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);

        return http_build_query($data, '', '&', $mergedOptions['encoding_type']);
    }

    /**
     * Export a collection of DTOs to query string format.
     */
    public function exportCollection(array $dataCollection, array $options = []): string
    {
        $defaultOptions = [
            'encoding_type' => PHP_QUERY_RFC1738,
            'wrap_in_data' => true,
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);
        $normalizedData = $this->normalizeCollection($dataCollection);

        $output = $mergedOptions['wrap_in_data']
            ? ['data' => $normalizedData]
            : $normalizedData;

        return http_build_query($output, '', '&', $mergedOptions['encoding_type']);
    }

    /**
     * Get the format identifier.
     */
    public function getFormat(): string
    {
        return 'query_string';
    }
}
