<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Export\Exporters;

use Grazulex\LaravelArc\Support\Export\AbstractExporter;

/**
 * Example custom exporter - HTML table exporter for DTOs.
 * This demonstrates how easy it is to add new export formats.
 */
final class HtmlExporter extends AbstractExporter
{
    /**
     * Export a single DTO to HTML table format.
     */
    public function export(array $data, array $options = []): string
    {
        $defaultOptions = [
            'table_class' => 'dto-table',
            'include_headers' => true,
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);

        $html = "<table class=\"{$mergedOptions['table_class']}\">";

        if ($mergedOptions['include_headers']) {
            $html .= '<thead><tr>';
            foreach (array_keys($data) as $header) {
                $html .= '<th>'.htmlspecialchars($header).'</th>';
            }
            $html .= '</tr></thead>';
        }

        $html .= '<tbody><tr>';
        foreach (array_values($data) as $value) {
            $html .= '<td>'.htmlspecialchars((string) $value).'</td>';
        }
        $html .= '</tr></tbody>';

        return $html . '</table>';
    }

    /**
     * Export a collection of DTOs to HTML table format.
     */
    public function exportCollection(array $dataCollection, array $options = []): string
    {
        $defaultOptions = [
            'table_class' => 'dto-collection-table',
            'include_headers' => true,
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);
        $normalizedData = $this->normalizeCollection($dataCollection);

        if ($normalizedData === []) {
            return '<table class="'.htmlspecialchars($mergedOptions['table_class']).'"><tbody><tr><td>No data</td></tr></tbody></table>';
        }

        $html = "<table class=\"{$mergedOptions['table_class']}\">";

        if ($mergedOptions['include_headers']) {
            $headers = array_keys($normalizedData[0]);
            $html .= '<thead><tr>';
            foreach ($headers as $header) {
                $html .= '<th>'.htmlspecialchars($header).'</th>';
            }
            $html .= '</tr></thead>';
        }

        $html .= '<tbody>';
        foreach ($normalizedData as $row) {
            $html .= '<tr>';
            foreach (array_values($row) as $value) {
                $html .= '<td>'.htmlspecialchars((string) $value).'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        return $html . '</table>';
    }

    /**
     * Get the format identifier.
     */
    public function getFormat(): string
    {
        return 'html';
    }
}
