<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Export\Exporters;

use Grazulex\LaravelArc\Support\Export\AbstractExporter;

/**
 * Markdown table exporter for DTOs.
 */
final class MarkdownExporter extends AbstractExporter
{
    /**
     * Export a single DTO to Markdown table format.
     */
    public function export(array $data, array $options = []): string
    {
        $defaultOptions = [
            'include_headers' => true,
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);

        if (! $mergedOptions['include_headers']) {
            return '| '.implode(' | ', array_values($data)).' |';
        }

        $headers = array_keys($data);
        $values = array_values($data);

        $output = '| '.implode(' | ', $headers).' |'."\n";
        $output .= '| '.str_repeat('--- | ', count($headers))."\n";

        return $output.('| '.implode(' | ', array_map([$this, 'formatValue'], $values)).' |');
    }

    /**
     * Export a collection of DTOs to Markdown table format.
     */
    public function exportCollection(array $dataCollection, array $options = []): string
    {
        $defaultOptions = [
            'include_headers' => true,
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);
        $normalizedData = $this->normalizeCollection($dataCollection);

        if ($normalizedData === []) {
            return '';
        }

        $headers = array_keys($normalizedData[0]);
        $output = '';

        if ($mergedOptions['include_headers']) {
            $output .= '| '.implode(' | ', $headers).' |'."\n";
            $output .= '| '.str_repeat('--- | ', count($headers))."\n";
        }

        foreach ($normalizedData as $row) {
            $values = array_values($row);
            $output .= '| '.implode(' | ', array_map([$this, 'formatValue'], $values)).' |'."\n";
        }

        return mb_rtrim($output, "\n");
    }

    /**
     * Get the format identifier.
     */
    public function getFormat(): string
    {
        return 'markdown';
    }

    /**
     * Format a value for Markdown table.
     */
    private function formatValue($value): string
    {
        if (is_null($value)) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        // Escape pipes in the value to prevent breaking the table
        return str_replace('|', '\\|', (string) $value);
    }
}
