<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Export;

use Grazulex\LaravelArc\Contracts\DtoCollectionExporter;
use Grazulex\LaravelArc\Contracts\DtoExporter;
use Grazulex\LaravelArc\Contracts\ExporterManager as ExporterManagerContract;
use InvalidArgumentException;

/**
 * Manager for DTO exporters.
 */
final class ExporterManager implements ExporterManagerContract
{
    /**
     * @var array<string, DtoExporter>
     */
    private array $exporters = [];

    /**
     * @var array<string, DtoCollectionExporter>
     */
    private array $collectionExporters = [];

    /**
     * Register a DTO exporter for a specific format.
     */
    public function registerExporter(string $format, DtoExporter $exporter): void
    {
        $this->exporters[$format] = $exporter;
    }

    /**
     * Register a DTO collection exporter for a specific format.
     */
    public function registerCollectionExporter(string $format, DtoCollectionExporter $exporter): void
    {
        $this->collectionExporters[$format] = $exporter;
    }

    /**
     * Get a DTO exporter for a specific format.
     */
    public function getExporter(string $format): DtoExporter
    {
        if (! $this->hasExporter($format)) {
            throw new InvalidArgumentException("No exporter registered for format: {$format}");
        }

        return $this->exporters[$format];
    }

    /**
     * Get a DTO collection exporter for a specific format.
     */
    public function getCollectionExporter(string $format): DtoCollectionExporter
    {
        if (! $this->hasCollectionExporter($format)) {
            throw new InvalidArgumentException("No collection exporter registered for format: {$format}");
        }

        return $this->collectionExporters[$format];
    }

    /**
     * Check if a format is supported for single DTO export.
     */
    public function hasExporter(string $format): bool
    {
        return isset($this->exporters[$format]);
    }

    /**
     * Check if a format is supported for collection export.
     */
    public function hasCollectionExporter(string $format): bool
    {
        return isset($this->collectionExporters[$format]);
    }

    /**
     * Get all registered formats for single DTO export.
     */
    public function getSupportedFormats(): array
    {
        return array_keys($this->exporters);
    }

    /**
     * Get all registered formats for collection export.
     */
    public function getSupportedCollectionFormats(): array
    {
        return array_keys($this->collectionExporters);
    }
}
