<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Export\Exporters;

use Grazulex\LaravelArc\Support\Export\AbstractExporter;
use RuntimeException;

/**
 * YAML exporter for DTOs.
 */
final class YamlExporter extends AbstractExporter
{
    /**
     * Export a single DTO to YAML format.
     */
    public function export(array $data, array $options = []): string
    {
        if (! function_exists('yaml_emit')) {
            throw new RuntimeException('YAML extension is not available. Install php-yaml extension.');
        }

        $defaultOptions = [
            'encoding' => YAML_UTF8_ENCODING,
            'linebreak' => YAML_LN_BREAK,
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);

        return yaml_emit($data, $mergedOptions['encoding'], $mergedOptions['linebreak']);
    }

    /**
     * Export a collection of DTOs to YAML format.
     */
    public function exportCollection(array $dataCollection, array $options = []): string
    {
        if (! function_exists('yaml_emit')) {
            throw new RuntimeException('YAML extension is not available. Install php-yaml extension.');
        }

        $defaultOptions = [
            'encoding' => YAML_UTF8_ENCODING,
            'linebreak' => YAML_LN_BREAK,
            'wrap_in_data' => true,
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);
        $normalizedData = $this->normalizeCollection($dataCollection);

        $output = $mergedOptions['wrap_in_data']
            ? ['data' => $normalizedData]
            : $normalizedData;

        return yaml_emit($output, $mergedOptions['encoding'], $mergedOptions['linebreak']);
    }

    /**
     * Get the format identifier.
     */
    public function getFormat(): string
    {
        return 'yaml';
    }
}
