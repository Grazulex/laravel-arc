<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;
use InvalidArgumentException;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use Throwable;

/**
 * Generates TypeScript declarations from YAML DTO definitions.
 *
 * The produced types describe the JSON shape of a DTO (the output of toArray()/toJson()):
 * every field is always present, and fields that are nullable or not required accept null.
 */
final class TypeScriptGenerator
{
    private const STRING_TYPES = [
        'string', 'text', 'longtext', 'mediumtext', 'varchar', 'char', 'uuid', 'id', 'email', 'url', 'slug',
        'phone', 'color', 'ip', 'ipv4', 'ipv6', 'ipaddress', 'mac', 'macaddress', 'currency', 'locale',
        'timezone', 'decimal', 'money', 'date', 'datetime', 'time', 'timestamp', 'binary', 'blob',
        'point', 'geometry', 'polygon', 'linestring', 'multipoint', 'multipolygon', 'multilinestring',
        'geometrycollection',
    ];

    private const NUMBER_TYPES = [
        'integer', 'int', 'bigint', 'biginteger', 'smallint', 'smallinteger', 'tinyint', 'tinyinteger',
        'unsignedint', 'unsignedbiginteger', 'float', 'double', 'number', 'year',
    ];

    private const BOOLEAN_TYPES = ['boolean', 'bool'];

    private const ARRAY_TYPES = ['array', 'set', 'collection'];

    private const OBJECT_TYPES = ['json', 'jsonb', 'object'];

    /**
     * Generate a TypeScript declaration for a parsed YAML definition.
     *
     * Options:
     *  - style: 'interface' (default) or 'type'
     *  - export: prefix declarations with "export" (default true)
     *  - imports: emit "import type" lines for nested DTOs (default false)
     *  - import_path: directory prefix used in import statements (default './')
     *
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $options
     */
    public function generate(array $definition, array $options = []): string
    {
        $options = array_merge([
            'style' => 'interface',
            'export' => true,
            'imports' => false,
            'import_path' => './',
        ], $options);

        if (! in_array($options['style'], ['interface', 'type'], true)) {
            throw new InvalidArgumentException("Unsupported TypeScript declaration style: {$options['style']}");
        }

        $name = $this->resolveTypeName($definition);
        $fields = $this->resolveFields($definition);

        $references = [];
        $lines = [];
        foreach ($fields as $fieldName => $config) {
            $config = is_array($config) ? $config : ['type' => (string) $config];
            $type = $this->mapField($config, $references);

            if ($this->isNullable($config)) {
                $type .= ' | null';
            }

            $lines[] = '  '.$this->formatKey((string) $fieldName).': '.$type.';';
        }

        $body = $lines === [] ? '{}' : "{\n".implode("\n", $lines)."\n}";
        $prefix = $options['export'] ? 'export ' : '';

        $declaration = $options['style'] === 'type'
            ? "{$prefix}type {$name} = {$body};\n"
            : "{$prefix}interface {$name} {$body}\n";

        unset($references[$name]);

        if (! $options['imports'] || $references === []) {
            return $declaration;
        }

        ksort($references);
        $importPath = (string) $options['import_path'];
        $imports = array_map(
            fn (string $reference): string => "import type { {$reference} } from '{$importPath}{$reference}';",
            array_keys($references)
        );

        return implode("\n", $imports)."\n\n".$declaration;
    }

    /**
     * Resolve the TypeScript type name of a definition (the DTO class short name).
     *
     * @param  array<string, mixed>  $definition
     */
    public function resolveTypeName(array $definition): string
    {
        $header = is_array($definition['header'] ?? null) ? $definition['header'] : [];
        $name = $header['dto'] ?? $header['class'] ?? $definition['class'] ?? null;

        if (! is_string($name) || $name === '') {
            throw new InvalidArgumentException('The definition has no DTO name (header.dto or header.class).');
        }

        return $this->shortName($name);
    }

    /**
     * Get the fields of a definition, including the fields added by behavioral traits.
     *
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    private function resolveFields(array $definition): array
    {
        $fields = is_array($definition['fields'] ?? null) ? $definition['fields'] : [];
        $header = is_array($definition['header'] ?? null) ? $definition['header'] : [];
        $traits = $definition['traits'] ?? $header['traits'] ?? [];

        if (! is_array($traits) || $traits === []) {
            return $fields;
        }

        $known = array_values(array_filter(
            $traits,
            fn ($trait): bool => is_string($trait) && BehavioralTraitRegistry::hasTrail($trait)
        ));

        return $known === [] ? $fields : BehavioralTraitRegistry::expandFields($fields, $known);
    }

    /**
     * Map a field definition to a TypeScript type.
     *
     * @param  array<string, mixed>  $config
     * @param  array<string, true>  $references
     */
    private function mapField(array $config, array &$references): string
    {
        $type = mb_strtolower((string) ($config['type'] ?? 'mixed'));

        if ($type === 'enum') {
            return $this->mapEnum($config);
        }

        if ($type === 'dto') {
            $dto = $config['dto'] ?? $config['class'] ?? null;
            if (! is_string($dto) || $dto === '') {
                return 'Record<string, unknown>';
            }

            $reference = $this->shortName($dto);
            $references[$reference] = true;

            return $reference;
        }

        if (in_array($type, self::ARRAY_TYPES, true)) {
            $items = $config['items'] ?? null;
            if (is_string($items)) {
                $items = ['type' => $items];
            }

            if (! is_array($items) || ! isset($items['type'])) {
                return 'unknown[]';
            }

            $itemType = $this->mapField($items, $references);

            return str_contains($itemType, ' ') ? "({$itemType})[]" : "{$itemType}[]";
        }

        return $this->mapScalar($type);
    }

    private function mapScalar(string $type): string
    {
        return match (true) {
            in_array($type, self::STRING_TYPES, true) => 'string',
            in_array($type, self::NUMBER_TYPES, true) => 'number',
            in_array($type, self::BOOLEAN_TYPES, true) => 'boolean',
            in_array($type, self::OBJECT_TYPES, true) => 'Record<string, unknown>',
            default => 'unknown',
        };
    }

    /**
     * Map an enum field to a union of literal types when its values are known.
     *
     * @param  array<string, mixed>  $config
     */
    private function mapEnum(array $config): string
    {
        $values = $config['values'] ?? null;
        if (is_array($values) && $values !== []) {
            return implode(' | ', array_map(fn ($value): string => $this->literal($value), array_values($values)));
        }

        $enumClass = $config['enum_class'] ?? $config['class'] ?? null;
        if (is_string($enumClass) && enum_exists($enumClass)) {
            try {
                $reflection = new ReflectionEnum($enumClass);
                $literals = [];
                foreach ($reflection->getCases() as $case) {
                    if ($case instanceof ReflectionEnumBackedCase) {
                        $literals[] = $this->literal($case->getBackingValue());
                    }
                }

                if ($literals !== []) {
                    return implode(' | ', $literals);
                }
            } catch (Throwable) {
                // Fall back to a plain string below
            }
        }

        return 'string';
    }

    private function literal(mixed $value): string
    {
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return "'".str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $value)."'";
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function isNullable(array $config): bool
    {
        return ($config['nullable'] ?? false) === true || ($config['required'] ?? true) === false;
    }

    private function formatKey(string $key): string
    {
        return preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*$/', $key) === 1
            ? $key
            : "'".str_replace(['\\', "'"], ['\\\\', "\\'"], $key)."'";
    }

    private function shortName(string $class): string
    {
        $class = mb_trim($class, '\\');
        $position = mb_strrpos($class, '\\');

        return $position === false ? $class : mb_substr($class, $position + 1);
    }
}
