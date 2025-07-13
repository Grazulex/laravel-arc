<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Export\Exporters;

use Grazulex\LaravelArc\Support\Export\AbstractExporter;

/**
 * CSV exporter for DTOs.
 */
final class CsvExporter extends AbstractExporter
{
    /**
     * Export a single DTO to CSV format.
     */
    public function export(array $data, array $options = []): string
    {
        $defaultOptions = [
            'delimiter' => ',',
            'enclosure' => '"',
            'escape' => '\\',
            'include_headers' => true,
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);

        $output = '';

        if ($mergedOptions['include_headers']) {
            $output .= $this->arrayToCsvLine(array_keys($data), $mergedOptions);
        }

        return $output . $this->arrayToCsvLine(array_values($data), $mergedOptions);
    }

    /**
     * Export a collection of DTOs to CSV format.
     */
    public function exportCollection(array $dataCollection, array $options = []): string
    {
        $defaultOptions = [
            'delimiter' => ',',
            'enclosure' => '"',
            'escape' => '\\',
            'include_headers' => true,
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);
        $normalizedData = $this->normalizeCollection($dataCollection);

        if ($normalizedData === []) {
            return '';
        }

        $output = '';

        if ($mergedOptions['include_headers']) {
            $headers = array_keys($normalizedData[0]);
            $output .= $this->arrayToCsvLine($headers, $mergedOptions);
        }

        foreach ($normalizedData as $row) {
            $output .= $this->arrayToCsvLine(array_values($row), $mergedOptions);
        }

        return $output;
    }

    /**
     * Get the format identifier.
     */
    public function getFormat(): string
    {
        return 'csv';
    }

    /**
     * Convert an array to a CSV line.
     */
    private function arrayToCsvLine(array $data, array $options): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $data, $options['delimiter'], $options['enclosure'], $options['escape']);
        rewind($handle);
        $line = fgets($handle);
        fclose($handle);

        return $line;
    }
}
