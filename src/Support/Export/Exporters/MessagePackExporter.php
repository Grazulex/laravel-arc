<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Export\Exporters;

use Grazulex\LaravelArc\Support\Export\AbstractExporter;
use RuntimeException;

/**
 * MessagePack exporter for DTOs.
 */
final class MessagePackExporter extends AbstractExporter
{
    /**
     * Export a single DTO to MessagePack format.
     */
    public function export(array $data, array $options = []): string
    {
        if (! function_exists('msgpack_pack')) {
            throw new RuntimeException('MessagePack extension is not available. Install php-msgpack extension.');
        }

        return msgpack_pack($data);
    }

    /**
     * Export a collection of DTOs to MessagePack format.
     */
    public function exportCollection(array $dataCollection, array $options = []): string
    {
        if (! function_exists('msgpack_pack')) {
            throw new RuntimeException('MessagePack extension is not available. Install php-msgpack extension.');
        }

        $defaultOptions = [
            'wrap_in_data' => true,
        ];

        $mergedOptions = $this->mergeOptions($defaultOptions, $options);
        $normalizedData = $this->normalizeCollection($dataCollection);

        $output = $mergedOptions['wrap_in_data']
            ? ['data' => $normalizedData]
            : $normalizedData;

        return msgpack_pack($output);
    }

    /**
     * Get the format identifier.
     */
    public function getFormat(): string
    {
        return 'msgpack';
    }
}
