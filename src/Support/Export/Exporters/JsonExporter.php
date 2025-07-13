<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Export\Exporters;

use Grazulex\LaravelArc\Support\Export\AbstractExporter;

/**
 * JSON exporter for DTOs.
 */
final class JsonExporter extends AbstractExporter
{
    /**
     * Export a single DTO to JSON format.
     */
    public function export(array $data, array $options = []): string
    {
        $defaultOptions = [
            'flags' => 0,
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);

        return json_encode($data, $mergedOptions['flags']);
    }

    /**
     * Export a collection of DTOs to JSON format.
     */
    public function exportCollection(array $dataCollection, array $options = []): string
    {
        $defaultOptions = [
            'flags' => 0,
            'wrap_in_data' => true,
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);
        $normalizedData = $this->normalizeCollection($dataCollection);

        $output = $mergedOptions['wrap_in_data']
            ? ['data' => $normalizedData]
            : $normalizedData;

        return json_encode($output, $mergedOptions['flags']);
    }

    /**
     * Get the format identifier.
     */
    public function getFormat(): string
    {
        return 'json';
    }
}
