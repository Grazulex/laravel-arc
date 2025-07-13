<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Export;

use Grazulex\LaravelArc\Contracts\ExporterManager as ExporterManagerContract;

/**
 * Factory for creating and configuring the ExporterManager.
 */
final class ExporterFactory
{
    private static ?ExporterManagerContract $instance = null;

    /**
     * Get the singleton instance of ExporterManager.
     */
    public static function getInstance(): ExporterManagerContract
    {
        if (! self::$instance instanceof ExporterManagerContract) {
            self::$instance = self::createDefaultManager();
        }

        return self::$instance;
    }

    /**
     * Set a custom ExporterManager instance.
     */
    public static function setInstance(ExporterManagerContract $manager): void
    {
        self::$instance = $manager;
    }

    /**
     * Reset the singleton instance (useful for testing).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Create a default ExporterManager with all built-in exporters.
     */
    public static function createDefaultManager(): ExporterManagerContract
    {
        $manager = new ExporterManager();

        // Register built-in exporters
        $exporters = [
            new Exporters\JsonExporter(),
            new Exporters\YamlExporter(),
            new Exporters\CsvExporter(),
            new Exporters\XmlExporter(),
            new Exporters\TomlExporter(),
            new Exporters\MarkdownExporter(),
            new Exporters\PhpArrayExporter(),
            new Exporters\QueryStringExporter(),
            new Exporters\MessagePackExporter(),
        ];

        foreach ($exporters as $exporter) {
            $format = $exporter->getFormat();
            $manager->registerExporter($format, $exporter);
            $manager->registerCollectionExporter($format, $exporter);
        }

        return $manager;
    }

    /**
     * Create a manager with custom exporters only.
     */
    public static function createEmptyManager(): ExporterManagerContract
    {
        return new ExporterManager();
    }
}
