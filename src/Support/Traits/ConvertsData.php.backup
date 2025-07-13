<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits;

use Grazulex\LaravelArc\Support\DtoCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use RuntimeException;
use SimpleXMLElement;

/**
 * Trait that provides data conversion functionality for DTOs.
 */
trait ConvertsData
{
    /**
     * Convert a collection of models to a specialized DTO collection.
     *
     * @param  iterable  $models  The models to convert
     * @return DtoCollection<int, static> Specialized DTO collection
     */
    public static function fromModels(iterable $models): DtoCollection
    {
        $dtos = collect($models)->map(fn ($model) => static::fromModel($model));

        return new DtoCollection($dtos);
    }

    /**
     * Convert a collection of models to a specialized DTO collection.
     * This is an alias for fromModels() for more intuitive usage.
     *
     * @param  iterable  $models  The models to convert
     * @return DtoCollection<int, static> Specialized DTO collection
     */
    public static function collection(iterable $models): DtoCollection
    {
        return static::fromModels($models);
    }

    /**
     * Convert a collection of models to a standard collection of DTOs.
     *
     * @param  iterable  $models  The models to convert
     * @return Collection<int, static> Standard collection of DTOs
     */
    public static function fromModelsAsCollection(iterable $models): Collection
    {
        return collect($models)->map(fn ($model) => static::fromModel($model));
    }

    /**
     * Convert a paginated collection of models to DTOs with pagination info.
     *
     * @param  LengthAwarePaginator|Paginator  $paginator  The paginated models
     * @return array{
     *     data: array<int, static>,
     *     meta: array{
     *         current_page: int,
     *         per_page: int,
     *         has_more_pages: bool,
     *         total?: int,        // Only for LengthAwarePaginator
     *         last_page?: int,    // Only for LengthAwarePaginator
     *         from?: int|null,    // Only for LengthAwarePaginator
     *         to?: int|null       // Only for LengthAwarePaginator
     *     }
     * } Paginated DTOs with meta information
     */
    public static function fromPaginator(LengthAwarePaginator|Paginator $paginator): array
    {
        $dtos = static::fromModels($paginator->items());

        $result = [
            'data' => $dtos->toArray(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
        ];

        // Add total info if available (LengthAwarePaginator)
        if ($paginator instanceof LengthAwarePaginator) {
            $result['meta'] = array_merge($result['meta'], [
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ]);
        }

        return $result;
    }

    /**
     * Convert a collection of models to a JSON collection (similar to Laravel Resources).
     *
     * @param  iterable  $models  The models to convert
     * @return string JSON representation of the DTO collection
     */
    public static function collectionToJson(iterable $models): string
    {
        $dtos = static::fromModels($models);

        return json_encode([
            'data' => $dtos->map(fn ($dto) => $dto->toArray())->toArray(),
        ]);
    }

    /**
     * Convert a collection of models to YAML format.
     *
     * @param  iterable  $models  The models to convert
     * @return string YAML representation of the DTO collection
     */
    public static function collectionToYaml(iterable $models): string
    {
        $dtos = static::fromModels($models);
        $data = ['data' => $dtos->map(fn ($dto) => $dto->toArray())->toArray()];

        if (! function_exists('yaml_emit')) {
            return static::toSimpleYamlStatic($data);
        }

        return yaml_emit($data, YAML_UTF8_ENCODING, YAML_LN_BREAK);
    }

    /**
     * Convert a collection of models to CSV format.
     *
     * @param  iterable  $models  The models to convert
     * @param  string  $delimiter  The field delimiter
     * @param  string  $enclosure  The field enclosure character
     * @param  string  $escape  The escape character
     * @param  bool  $includeHeaders  Whether to include column headers
     * @return string CSV representation of the DTO collection
     */
    public static function collectionToCsv(iterable $models, string $delimiter = ',', string $enclosure = '"', string $escape = '\\', bool $includeHeaders = true): string
    {
        $dtos = static::fromModels($models);
        $data = $dtos->map(fn ($dto) => $dto->toArray())->toArray();

        if (empty($data)) {
            return '';
        }

        $output = '';
        $headers = array_keys($data[0]);

        if ($includeHeaders) {
            $output .= static::arrayToCsvLineStatic($headers, $delimiter, $enclosure, $escape)."\n";
        }

        foreach ($data as $row) {
            $output .= static::arrayToCsvLineStatic(array_values($row), $delimiter, $enclosure, $escape)."\n";
        }

        return mb_rtrim($output);
    }

    /**
     * Convert a collection of models to XML format.
     *
     * @param  iterable  $models  The models to convert
     * @param  string  $rootElement  The root XML element name
     * @param  string  $itemElement  The item XML element name
     * @param  string  $encoding  The XML encoding
     * @return string XML representation of the DTO collection
     */
    public static function collectionToXml(iterable $models, string $rootElement = 'collection', string $itemElement = 'item', string $encoding = 'UTF-8'): string
    {
        $dtos = static::fromModels($models);
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"{$encoding}\"?><{$rootElement}></{$rootElement}>");

        foreach ($dtos as $dto) {
            $itemXml = $xml->addChild($itemElement);
            static::arrayToXmlStatic($dto->toArray(), $itemXml);
        }

        return $xml->asXML();
    }

    /**
     * Convert a collection of models to Markdown table format.
     *
     * @param  iterable  $models  The models to convert
     * @param  bool  $includeHeaders  Whether to include table headers
     * @return string Markdown table representation of the DTO collection
     */
    public static function collectionToMarkdownTable(iterable $models, bool $includeHeaders = true): string
    {
        $dtos = static::fromModels($models);
        $data = $dtos->map(fn ($dto) => $dto->toArray())->toArray();

        if (empty($data)) {
            return '';
        }

        $output = '';
        $headers = array_keys($data[0]);

        if ($includeHeaders) {
            $output .= '| '.implode(' | ', $headers)." |\n";
            $output .= '| '.implode(' | ', array_fill(0, count($headers), '---'))." |\n";
        }

        foreach ($data as $row) {
            $values = array_map(fn ($value): string|false => is_array($value) ? json_encode($value) : (string) $value, array_values($row));
            $output .= '| '.implode(' | ', $values)." |\n";
        }

        return $output;
    }

    /**
     * Convert the DTO to JSON.
     *
     * @param  int  $options  JSON encoding options
     * @return string The JSON representation
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Convert the DTO to YAML format.
     *
     * @return string The YAML representation
     */
    public function toYaml(): string
    {
        if (! function_exists('yaml_emit')) {
            // Fallback to simple YAML-like format if YAML extension not available
            return $this->toSimpleYaml($this->toArray());
        }

        return yaml_emit($this->toArray(), YAML_UTF8_ENCODING, YAML_LN_BREAK);
    }

    /**
     * Convert the DTO to CSV format.
     *
     * @param  string  $delimiter  The field delimiter
     * @param  string  $enclosure  The field enclosure character
     * @param  string  $escape  The escape character
     * @param  bool  $includeHeaders  Whether to include column headers
     * @return string The CSV representation
     */
    public function toCsv(string $delimiter = ',', string $enclosure = '"', string $escape = '\\', bool $includeHeaders = true): string
    {
        $data = $this->toArray();
        $output = '';

        if ($includeHeaders) {
            $output .= $this->arrayToCsvLine(array_keys($data), $delimiter, $enclosure, $escape)."\n";
        }

        return $output.$this->arrayToCsvLine(array_values($data), $delimiter, $enclosure, $escape);
    }

    /**
     * Convert the DTO to XML format.
     *
     * @param  string  $rootElement  The root XML element name
     * @param  string  $encoding  The XML encoding
     * @return string The XML representation
     */
    public function toXml(string $rootElement = 'dto', string $encoding = 'UTF-8'): string
    {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"{$encoding}\"?><{$rootElement}></{$rootElement}>");
        $this->arrayToXml($this->toArray(), $xml);

        return $xml->asXML();
    }

    /**
     * Convert the DTO to MessagePack format (binary).
     *
     * @return string The MessagePack binary representation
     *
     * @throws RuntimeException If MessagePack extension is not available
     */
    public function toMessagePack(): string
    {
        if (! function_exists('msgpack_pack')) {
            throw new RuntimeException('MessagePack extension is required for toMessagePack() method');
        }

        return msgpack_pack($this->toArray());
    }

    /**
     * Convert the DTO to TOML format.
     *
     * @return string The TOML representation
     */
    public function toToml(): string
    {
        // Simple TOML format implementation
        $data = $this->toArray();
        $output = '';

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $output .= "[{$key}]\n";
                foreach ($value as $subKey => $subValue) {
                    $output .= "{$subKey} = ".$this->toTomlValue($subValue)."\n";
                }
                $output .= "\n";
            } else {
                $output .= "{$key} = ".$this->toTomlValue($value)."\n";
            }
        }

        return mb_rtrim($output);
    }

    /**
     * Convert the DTO to Markdown table format.
     *
     * @param  bool  $includeHeaders  Whether to include table headers
     * @return string The Markdown table representation
     */
    public function toMarkdownTable(bool $includeHeaders = true): string
    {
        $data = $this->toArray();
        $output = '';

        if ($includeHeaders) {
            $headers = array_keys($data);
            $output .= '| '.implode(' | ', $headers)." |\n";
            $output .= '| '.implode(' | ', array_fill(0, count($headers), '---'))." |\n";
        }

        $values = array_map(fn ($value): string|false => is_array($value) ? json_encode($value) : (string) $value, array_values($data));

        return $output.('| '.implode(' | ', $values)." |\n");
    }

    /**
     * Convert the DTO to PHP array export format (var_export style).
     *
     * @return string The PHP array representation
     */
    public function toPhpArray(): string
    {
        return var_export($this->toArray(), true);
    }

    /**
     * Convert the DTO to a query string format.
     *
     * @return string The query string representation
     */
    public function toQueryString(): string
    {
        return http_build_query($this->toArray());
    }

    /**
     * Convert the DTO to a collection.
     *
     * @return Collection<string, mixed> The collection representation
     */
    public function toCollection(): Collection
    {
        return collect($this->toArray());
    }

    /**
     * Get only the specified keys from the DTO.
     *
     * @param  array  $keys  The keys to include
     * @return array The filtered array
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->toArray(), array_flip($keys));
    }

    /**
     * Get all keys except the specified ones from the DTO.
     *
     * @param  array  $keys  The keys to exclude
     * @return array The filtered array
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->toArray(), array_flip($keys));
    }

    /**
     * Static version of toSimpleYaml for collection methods.
     *
     * @param  array  $data  The data to convert
     * @param  int  $indent  Current indentation level
     * @return string The YAML-like representation
     */
    private static function toSimpleYamlStatic(array $data, int $indent = 0): string
    {
        $output = '';
        $spaces = str_repeat('  ', $indent);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $output .= $spaces.$key.":\n";
                $output .= static::toSimpleYamlStatic($value, $indent + 1);
            } else {
                $value = is_string($value) ? "\"$value\"" : (string) $value;
                $output .= $spaces.$key.': '.$value."\n";
            }
        }

        return $output;
    }

    /**
     * Static version of arrayToCsvLine for collection methods.
     *
     * @param  array  $data  The data array
     * @param  string  $delimiter  Field delimiter
     * @param  string  $enclosure  Field enclosure
     * @param  string  $escape  Escape character
     * @return string The CSV line
     */
    private static function arrayToCsvLineStatic(array $data, string $delimiter, string $enclosure, string $escape): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, array_map(fn ($value): string|false => is_array($value) ? json_encode($value) : (string) $value, $data), $delimiter, $enclosure, $escape);
        rewind($handle);
        $csv = mb_rtrim(fgets($handle), "\n");
        fclose($handle);

        return $csv;
    }

    /**
     * Static version of arrayToXml for collection methods.
     *
     * @param  array  $data  The data array
     * @param  SimpleXMLElement  $xml  The XML element
     */
    private static function arrayToXmlStatic(array $data, SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subnode = $xml->addChild(is_numeric($key) ? 'item' : $key);
                static::arrayToXmlStatic($value, $subnode);
            } else {
                $xml->addChild(is_numeric($key) ? 'item' : $key, htmlspecialchars((string) $value));
            }
        }
    }

    /**
     * Convert value to TOML format.
     *
     * @param  mixed  $value  The value to convert
     * @return string The TOML formatted value
     */
    private function toTomlValue(mixed $value): string
    {
        if (is_string($value)) {
            return '"'.addslashes($value).'"';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return '""';
        }

        if (is_array($value)) {
            return '['.implode(', ', array_map([$this, 'toTomlValue'], $value)).']';
        }

        return (string) $value;
    }

    /**
     * Simple YAML-like format fallback when YAML extension is not available.
     *
     * @param  array  $data  The data to convert
     * @param  int  $indent  Current indentation level
     * @return string The YAML-like representation
     */
    private function toSimpleYaml(array $data, int $indent = 0): string
    {
        $output = '';
        $spaces = str_repeat('  ', $indent);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $output .= $spaces.$key.":\n";
                $output .= $this->toSimpleYaml($value, $indent + 1);
            } else {
                $value = is_string($value) ? "\"$value\"" : (string) $value;
                $output .= $spaces.$key.': '.$value."\n";
            }
        }

        return $output;
    }

    /**
     * Convert array to CSV line.
     *
     * @param  array  $data  The data array
     * @param  string  $delimiter  Field delimiter
     * @param  string  $enclosure  Field enclosure
     * @param  string  $escape  Escape character
     * @return string The CSV line
     */
    private function arrayToCsvLine(array $data, string $delimiter, string $enclosure, string $escape): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, array_map(fn ($value): string|false => is_array($value) ? json_encode($value) : (string) $value, $data), $delimiter, $enclosure, $escape);
        rewind($handle);
        $csv = mb_rtrim(fgets($handle), "\n");
        fclose($handle);

        return $csv;
    }

    /**
     * Convert array to XML recursively.
     *
     * @param  array  $data  The data array
     * @param  SimpleXMLElement  $xml  The XML element
     */
    private function arrayToXml(array $data, SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subnode = $xml->addChild(is_numeric($key) ? 'item' : $key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xml->addChild(is_numeric($key) ? 'item' : $key, htmlspecialchars((string) $value));
            }
        }
    }
}
