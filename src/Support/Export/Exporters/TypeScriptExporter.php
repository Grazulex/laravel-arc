<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Export\Exporters;

use BackedEnum;
use DateTimeInterface;
use Grazulex\LaravelArc\Support\Export\AbstractExporter;
use InvalidArgumentException;
use JsonSerializable;
use UnitEnum;

/**
 * TypeScript exporter for DTOs.
 *
 * Infers a TypeScript interface (or type alias) from exported DTO data.
 * To generate types from YAML definitions instead, use the dto:generate-ts command.
 */
final class TypeScriptExporter extends AbstractExporter
{
    private const INDENT = '  ';

    private const DEFAULT_OPTIONS = [
        'name' => 'Dto',
        'style' => 'interface',
        'export' => true,
    ];

    /**
     * Export a single DTO to a TypeScript declaration.
     */
    public function export(array $data, array $options = []): string
    {
        $options = $this->resolveOptions($options);

        return $this->declare($options['name'], $this->shapeOf($data), $options);
    }

    /**
     * Export a collection of DTOs to TypeScript declarations.
     *
     * Keys missing from some items are marked optional, and types seen across items are merged.
     * A "{name}Collection" type alias is added, matching the JSON collection export shape.
     */
    public function exportCollection(array $dataCollection, array $options = []): string
    {
        $options = $this->resolveOptions(array_merge(['wrap_in_data' => true], $options));
        $items = $this->normalizeCollection($dataCollection);

        $shape = [];
        foreach ($items as $item) {
            foreach ($this->shapeOf($item) as $key => $types) {
                $shape[$key] = array_merge($shape[$key] ?? [], $types);
            }
        }

        $optional = [];
        foreach (array_keys($shape) as $key) {
            foreach ($items as $item) {
                if (! array_key_exists($key, $item)) {
                    $optional[$key] = true;
                    break;
                }
            }
        }

        $name = $options['name'];
        $prefix = $options['export'] ? 'export ' : '';
        $collection = $options['wrap_in_data']
            ? "{$prefix}type {$name}Collection = {\n".self::INDENT."data: {$name}[];\n};\n"
            : "{$prefix}type {$name}Collection = {$name}[];\n";

        return $this->declare($name, $shape, $options, $optional)."\n".$collection;
    }

    /**
     * Get the format identifier.
     */
    public function getFormat(): string
    {
        return 'typescript';
    }

    /**
     * @return array{name: string, style: string, export: bool, wrap_in_data?: bool}
     */
    private function resolveOptions(array $options): array
    {
        $options = $this->mergeOptions(self::DEFAULT_OPTIONS, $options);

        if (! is_string($options['name']) || preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*$/', $options['name']) !== 1) {
            throw new InvalidArgumentException('The TypeScript type name must be a valid identifier.');
        }

        if (! in_array($options['style'], ['interface', 'type'], true)) {
            throw new InvalidArgumentException("Unsupported TypeScript declaration style: {$options['style']}");
        }

        $options['export'] = (bool) $options['export'];

        return $options;
    }

    /**
     * @param  array<string, array<string, true>>  $shape
     * @param  array<string, true>  $optional
     */
    private function declare(string $name, array $shape, array $options, array $optional = []): string
    {
        $body = $this->renderObject($shape, 1, $optional);
        $prefix = $options['export'] ? 'export ' : '';

        return $options['style'] === 'type'
            ? "{$prefix}type {$name} = {$body};\n"
            : "{$prefix}interface {$name} {$body}\n";
    }

    /**
     * Build the shape of an associative array: key => set of TypeScript types.
     *
     * @return array<string, array<string, true>>
     */
    private function shapeOf(array $data): array
    {
        $shape = [];
        foreach ($data as $key => $value) {
            $shape[(string) $key] = [$this->typeOf($value) => true];
        }

        return $shape;
    }

    private function typeOf(mixed $value, int $depth = 1): string
    {
        if ($depth > 32) {
            return 'unknown';
        }

        return match (true) {
            $value === null => 'null',
            is_bool($value) => 'boolean',
            is_int($value), is_float($value) => 'number',
            is_string($value) => 'string',
            is_array($value) => $this->typeOfArray($value, $depth),
            is_object($value) => $this->typeOfObject($value, $depth),
            default => 'unknown',
        };
    }

    private function typeOfArray(array $value, int $depth): string
    {
        if ($value === []) {
            return 'unknown[]';
        }

        if (array_is_list($value)) {
            $types = [];
            foreach ($value as $item) {
                // List items are rendered on the same line as their parent, so they keep its depth
                $types[$this->typeOf($item, $depth)] = true;
            }

            $union = $this->union($types);

            return count($types) > 1 ? "({$union})[]" : "{$union}[]";
        }

        $shape = [];
        foreach ($value as $key => $item) {
            $shape[(string) $key] = [$this->typeOf($item, $depth + 1) => true];
        }

        return $this->renderObject($shape, $depth + 1);
    }

    private function typeOfObject(object $value, int $depth): string
    {
        if ($value instanceof BackedEnum) {
            return is_int($value->value) ? 'number' : 'string';
        }

        if ($value instanceof UnitEnum || $value instanceof DateTimeInterface) {
            return 'string';
        }

        if ($value instanceof JsonSerializable) {
            return $this->typeOf($value->jsonSerialize(), $depth);
        }

        if (method_exists($value, 'toArray')) {
            $array = $value->toArray();

            return is_array($array) ? $this->typeOfArray($array, $depth) : 'unknown';
        }

        $properties = get_object_vars($value);

        return $properties === [] ? 'Record<string, unknown>' : $this->typeOfArray($properties, $depth);
    }

    /**
     * @param  array<string, array<string, true>>  $shape
     * @param  array<string, true>  $optional
     */
    private function renderObject(array $shape, int $depth, array $optional = []): string
    {
        if ($shape === []) {
            return '{}';
        }

        $padding = str_repeat(self::INDENT, $depth);
        $lines = [];
        foreach ($shape as $key => $types) {
            $marker = isset($optional[$key]) ? '?' : '';
            $lines[] = $padding.$this->formatKey((string) $key).$marker.': '.$this->union($types).';';
        }

        return "{\n".implode("\n", $lines)."\n".str_repeat(self::INDENT, $depth - 1).'}';
    }

    /**
     * @param  array<string, true>  $types
     */
    private function union(array $types): string
    {
        $names = array_keys($types);

        // Keep "null" last for readability
        $hasNull = in_array('null', $names, true);
        $names = array_values(array_filter($names, fn (string $name): bool => $name !== 'null'));
        if ($hasNull) {
            $names[] = 'null';
        }

        return implode(' | ', $names);
    }

    private function formatKey(string $key): string
    {
        return preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*$/', $key) === 1
            ? $key
            : "'".str_replace(['\\', "'"], ['\\\\', "\\'"], $key)."'";
    }
}
