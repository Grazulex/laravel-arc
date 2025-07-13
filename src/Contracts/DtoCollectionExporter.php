<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Contracts;

/**
 * Contract for exporting collections of DTOs to various formats.
 */
interface DtoCollectionExporter
{
    /**
     * Export a collection of DTOs to the target format.
     *
     * @param  array  $dataCollection  Array of DTO data arrays
     * @param  array  $options  Optional configuration for the export
     * @return string The exported data as a string
     */
    public function exportCollection(array $dataCollection, array $options = []): string;

    /**
     * Get the format identifier for this exporter.
     *
     * @return string The format identifier (e.g., 'json', 'csv', 'xml')
     */
    public function getFormat(): string;
}
