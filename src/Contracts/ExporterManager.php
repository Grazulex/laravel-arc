<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Contracts;

use InvalidArgumentException;

/**
 * Contract for managing DTO exporters.
 */
interface ExporterManager
{
    /**
     * Register a DTO exporter for a specific format.
     *
     * @param  string  $format  The format identifier
     * @param  DtoExporter  $exporter  The exporter instance
     */
    public function registerExporter(string $format, DtoExporter $exporter): void;

    /**
     * Register a DTO collection exporter for a specific format.
     *
     * @param  string  $format  The format identifier
     * @param  DtoCollectionExporter  $exporter  The collection exporter instance
     */
    public function registerCollectionExporter(string $format, DtoCollectionExporter $exporter): void;

    /**
     * Get a DTO exporter for a specific format.
     *
     * @param  string  $format  The format identifier
     *
     * @throws InvalidArgumentException When the format is not supported
     */
    public function getExporter(string $format): DtoExporter;

    /**
     * Get a DTO collection exporter for a specific format.
     *
     * @param  string  $format  The format identifier
     *
     * @throws InvalidArgumentException When the format is not supported
     */
    public function getCollectionExporter(string $format): DtoCollectionExporter;

    /**
     * Check if a format is supported for single DTO export.
     *
     * @param  string  $format  The format identifier
     */
    public function hasExporter(string $format): bool;

    /**
     * Check if a format is supported for collection export.
     *
     * @param  string  $format  The format identifier
     */
    public function hasCollectionExporter(string $format): bool;

    /**
     * Get all registered formats for single DTO export.
     *
     * @return array<string>
     */
    public function getSupportedFormats(): array;

    /**
     * Get all registered formats for collection export.
     *
     * @return array<string>
     */
    public function getSupportedCollectionFormats(): array;
}
