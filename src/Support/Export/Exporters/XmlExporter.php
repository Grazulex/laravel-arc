<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Export\Exporters;

use Grazulex\LaravelArc\Support\Export\AbstractExporter;
use SimpleXMLElement;

/**
 * XML exporter for DTOs.
 */
final class XmlExporter extends AbstractExporter
{
    /**
     * Export a single DTO to XML format.
     */
    public function export(array $data, array $options = []): string
    {
        $defaultOptions = [
            'root_element' => 'dto',
            'encoding' => 'UTF-8',
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);

        $xml = new SimpleXMLElement(
            "<?xml version=\"1.0\" encoding=\"{$mergedOptions['encoding']}\"?><{$mergedOptions['root_element']}></{$mergedOptions['root_element']}>"
        );

        $this->arrayToXml($data, $xml);

        return $xml->asXML();
    }

    /**
     * Export a collection of DTOs to XML format.
     */
    public function exportCollection(array $dataCollection, array $options = []): string
    {
        $defaultOptions = [
            'root_element' => 'collection',
            'item_element' => 'item',
            'encoding' => 'UTF-8',
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);
        $normalizedData = $this->normalizeCollection($dataCollection);

        $xml = new SimpleXMLElement(
            "<?xml version=\"1.0\" encoding=\"{$mergedOptions['encoding']}\"?><{$mergedOptions['root_element']}></{$mergedOptions['root_element']}>"
        );

        foreach ($normalizedData as $item) {
            $itemElement = $xml->addChild($mergedOptions['item_element']);
            $this->arrayToXml($item, $itemElement);
        }

        return $xml->asXML();
    }

    /**
     * Get the format identifier.
     */
    public function getFormat(): string
    {
        return 'xml';
    }

    /**
     * Convert an array to XML elements.
     */
    private function arrayToXml(array $data, SimpleXMLElement $xmlElement): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subElement = $xmlElement->addChild($key);
                $this->arrayToXml($value, $subElement);
            } else {
                $xmlElement->addChild($key, htmlspecialchars((string) $value));
            }
        }
    }
}
