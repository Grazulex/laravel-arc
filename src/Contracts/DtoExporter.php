<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Contracts;

/**
 * Contract for exporting single DTO instances to various formats.
 */
interface DtoExporter
{
    /**
     * Export a single DTO to the target format.
     *
     * @param  array  $data  The DTO data as an array
     * @param  array  $options  Optional configuration for the export
     * @return string The exported data as a string
     */
    public function export(array $data, array $options = []): string;

    /**
     * Get the format identifier for this exporter.
     *
     * @return string The format identifier (e.g., 'json', 'csv', 'xml')
     */
    public function getFormat(): string;
}
