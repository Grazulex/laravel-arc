<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Export\Exporters;

use Grazulex\LaravelArc\Support\Export\AbstractExporter;

/**
 * TOML exporter for DTOs.
 */
final class TomlExporter extends AbstractExporter
{
    /**
     * Export a single DTO to TOML format.
     */
    public function export(array $data, array $options = []): string
    {
        return $this->arrayToToml($data);
    }

    /**
     * Export a collection of DTOs to TOML format.
     */
    public function exportCollection(array $dataCollection, array $options = []): string
    {
        $normalizedData = $this->normalizeCollection($dataCollection);

        if ($normalizedData === []) {
            return '';
        }

        $output = '';
        foreach ($normalizedData as $item) {
            $output .= "[[data]]\n";
            $output .= $this->arrayToToml($item);
            $output .= "\n";
        }

        return mb_rtrim($output, "\n");
    }

    /**
     * Get the format identifier.
     */
    public function getFormat(): string
    {
        return 'toml';
    }

    /**
     * Convert an array to TOML format.
     */
    private function arrayToToml(array $data): string
    {
        $output = '';

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // For nested arrays, we'd need more complex TOML handling
                // For now, convert to simple string representation
                $value = json_encode($value);
            } elseif (is_string($value)) {
                $value = '"'.addslashes($value).'"';
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                continue; // Skip null values in TOML
            }

            $output .= "{$key} = {$value}\n";
        }

        return $output;
    }
}
