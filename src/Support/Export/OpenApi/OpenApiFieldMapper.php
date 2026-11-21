<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Export\OpenApi;

use InvalidArgumentException;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use Throwable;

/**
 * Maps a YAML DTO field definition to an OpenAPI schema object.
 *
 * The mapping covers the field type, nullability, enums, nested DTOs (as $ref),
 * arrays, documentation keys (description, example, default, format) and the
 * constraints that can be derived from the validation rules (min, max, between,
 * size, gt, gte, lt, lte, regex, in, distinct, email, url, uuid, ip...).
 */
final class OpenApiFieldMapper
{
    public const DEFAULT_REF_PREFIX = '#/components/schemas/';

    /**
     * Type => [OpenAPI type, OpenAPI format|null].
     *
     * @var array<string, array{0: string, 1: string|null}>
     */
    private const TYPE_MAP = [
        // Strings
        'string' => ['string', null],
        'text' => ['string', null],
        'longtext' => ['string', null],
        'mediumtext' => ['string', null],
        'varchar' => ['string', null],
        'char' => ['string', null],
        'id' => ['string', null],
        'slug' => ['string', null],
        'phone' => ['string', null],
        'color' => ['string', null],
        'currency' => ['string', null],
        'locale' => ['string', null],
        'timezone' => ['string', null],
        'mac' => ['string', null],
        'macaddress' => ['string', null],
        'uuid' => ['string', 'uuid'],
        'email' => ['string', 'email'],
        'url' => ['string', 'uri'],
        'ip' => ['string', 'ipv4'],
        'ipv4' => ['string', 'ipv4'],
        'ipaddress' => ['string', 'ipv4'],
        'ipv6' => ['string', 'ipv6'],
        'decimal' => ['string', 'decimal'],
        'money' => ['string', 'decimal'],
        'date' => ['string', 'date'],
        'datetime' => ['string', 'date-time'],
        'timestamp' => ['string', 'date-time'],
        'time' => ['string', 'time'],
        'binary' => ['string', 'binary'],
        'blob' => ['string', 'binary'],
        'point' => ['string', null],
        'geometry' => ['string', null],
        'polygon' => ['string', null],
        'linestring' => ['string', null],
        'multipoint' => ['string', null],
        'multipolygon' => ['string', null],
        'multilinestring' => ['string', null],
        'geometrycollection' => ['string', null],
        // Integers
        'integer' => ['integer', null],
        'int' => ['integer', null],
        'smallint' => ['integer', null],
        'smallinteger' => ['integer', null],
        'tinyint' => ['integer', null],
        'tinyinteger' => ['integer', null],
        'unsignedint' => ['integer', null],
        'year' => ['integer', null],
        'bigint' => ['integer', 'int64'],
        'biginteger' => ['integer', 'int64'],
        'unsignedbiginteger' => ['integer', 'int64'],
        // Numbers
        'float' => ['number', 'float'],
        'double' => ['number', 'double'],
        'number' => ['number', null],
        // Booleans
        'boolean' => ['boolean', null],
        'bool' => ['boolean', null],
        // Arrays
        'array' => ['array', null],
        'set' => ['array', null],
        'collection' => ['array', null],
        // Objects
        'json' => ['object', null],
        'jsonb' => ['object', null],
        'object' => ['object', null],
    ];

    /**
     * Laravel validation rules that only describe a string format.
     *
     * @var array<string, string>
     */
    private const FORMAT_RULES = [
        'email' => 'email',
        'url' => 'uri',
        'active_url' => 'uri',
        'uuid' => 'uuid',
        'ulid' => 'ulid',
        'ip' => 'ipv4',
        'ipv4' => 'ipv4',
        'ipv6' => 'ipv6',
        'date' => 'date',
        'json' => 'json',
    ];

    private bool $legacyNullable;

    public function __construct(
        string $openApiVersion = '3.1.0',
        private string $refPrefix = self::DEFAULT_REF_PREFIX,
    ) {
        if (preg_match('/^3\.[01](\.\d+)?$/', $openApiVersion) !== 1) {
            throw new InvalidArgumentException("Unsupported OpenAPI version: {$openApiVersion} (supported: 3.0, 3.1)");
        }

        $this->legacyNullable = str_starts_with($openApiVersion, '3.0');
    }

    public static function shortName(string $class): string
    {
        $class = mb_trim($class, '\\');
        $position = mb_strrpos($class, '\\');

        return $position === false ? $class : mb_substr($class, $position + 1);
    }

    /**
     * Map a field definition to an OpenAPI schema.
     *
     * @param  array<string, mixed>|string  $config
     * @return array<string, mixed>
     */
    public function map(array|string $config): array
    {
        $config = $this->normalizeConfig($config);
        $schema = $this->mapType($config);
        $schema = $this->applyDocumentation($schema, $config);

        return $this->isNullable($config) ? $this->makeNullable($schema) : $schema;
    }

    /**
     * Map a relation definition (hasOne, belongsTo, hasMany, belongsToMany) to an OpenAPI schema.
     *
     * @param  array<string, mixed>  $relation
     * @return array<string, mixed>
     */
    public function mapRelation(array $relation): array
    {
        $type = mb_strtolower(str_replace('_', '', (string) ($relation['type'] ?? '')));
        $dto = $relation['dto'] ?? $relation['class'] ?? null;
        $target = is_string($dto) && $dto !== '' ? $this->reference($dto) : ['type' => 'object'];

        $schema = in_array($type, ['hasmany', 'belongstomany', 'morphmany', 'morphtomany'], true)
            ? ['type' => 'array', 'items' => $target]
            : $target;

        $schema = $this->applyDocumentation($schema, $relation);

        return $this->isNullable($relation) ? $this->makeNullable($schema) : $schema;
    }

    /**
     * Whether a field or relation must be listed in the "required" array of its schema.
     *
     * @param  array<string, mixed>|string  $config
     */
    public function isRequired(array|string $config): bool
    {
        $config = $this->normalizeConfig($config);

        return ($config['required'] ?? true) !== false;
    }

    /**
     * Whether a field or relation accepts null.
     *
     * @param  array<string, mixed>|string  $config
     */
    public function isNullable(array|string $config): bool
    {
        $config = $this->normalizeConfig($config);

        if (($config['nullable'] ?? false) === true || ($config['required'] ?? true) === false) {
            return true;
        }

        return in_array('nullable', $this->rules($config), true);
    }

    /**
     * Build a $ref schema for a DTO class name.
     *
     * @return array{'$ref': string}
     */
    public function reference(string $class): array
    {
        return ['$ref' => $this->refPrefix.self::shortName($class)];
    }

    /**
     * @param  array<string, mixed>|string  $config
     * @return array<string, mixed>
     */
    private function normalizeConfig(array|string $config): array
    {
        return is_array($config) ? $config : ['type' => $config];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function mapType(array $config): array
    {
        $type = mb_strtolower((string) ($config['type'] ?? 'mixed'));

        if ($type === 'enum') {
            return $this->applyConstraints($this->mapEnum($config), $config);
        }

        if ($type === 'dto') {
            $dto = $config['dto'] ?? $config['class'] ?? null;

            return is_string($dto) && $dto !== '' ? $this->reference($dto) : ['type' => 'object'];
        }

        if (! isset(self::TYPE_MAP[$type])) {
            // Unknown types accept any value
            return [];
        }

        [$openApiType, $format] = self::TYPE_MAP[$type];
        $schema = ['type' => $openApiType];

        if ($format !== null) {
            $schema['format'] = $format;
        }

        if ($openApiType === 'array') {
            $items = $config['items'] ?? null;
            $schema['items'] = is_string($items) || (is_array($items) && isset($items['type']))
                ? $this->map($items)
                : [];
        }

        $isScalar = $openApiType !== 'array' && $openApiType !== 'object';
        if ($isScalar && isset($config['enum']) && is_array($config['enum']) && $config['enum'] !== []) {
            $schema['enum'] = array_values($config['enum']);
        }

        return $this->applyConstraints($schema, $config);
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function mapEnum(array $config): array
    {
        $values = $config['values'] ?? $config['enum'] ?? null;

        if (! is_array($values) || $values === []) {
            $values = $this->enumClassValues($config['enum_class'] ?? $config['class'] ?? null);
        }

        if ($values === null || $values === []) {
            return ['type' => 'string'];
        }

        $values = array_values($values);

        return ['type' => $this->scalarTypeOf($values), 'enum' => $values];
    }

    /**
     * @return list<int|string>|null
     */
    private function enumClassValues(mixed $enumClass): ?array
    {
        if (! is_string($enumClass) || ! enum_exists($enumClass)) {
            return null;
        }

        try {
            $values = [];
            foreach ((new ReflectionEnum($enumClass))->getCases() as $case) {
                $values[] = $case instanceof ReflectionEnumBackedCase ? $case->getBackingValue() : $case->getName();
            }

            return $values;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  list<mixed>  $values
     */
    private function scalarTypeOf(array $values): string
    {
        $types = array_values(array_unique(array_map(fn (mixed $value): string => match (true) {
            is_int($value) => 'integer',
            is_float($value) => 'number',
            is_bool($value) => 'boolean',
            default => 'string',
        }, $values)));

        if (count($types) === 1) {
            return $types[0];
        }

        return array_diff($types, ['integer', 'number']) === [] ? 'number' : 'string';
    }

    /**
     * Apply the constraints declared as field keys and as validation rules.
     *
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function applyConstraints(array $schema, array $config): array
    {
        $type = $schema['type'] ?? null;

        foreach (['min_length' => 'min', 'max_length' => 'max'] as $key => $bound) {
            if (isset($config[$key]) && is_numeric($config[$key])) {
                $schema = $this->applyBound($schema, $bound, $this->number($config[$key]), false);
            }
        }

        foreach (['min', 'max'] as $bound) {
            if (isset($config[$bound]) && is_numeric($config[$bound])) {
                $schema = $this->applyBound($schema, $bound, $this->number($config[$bound]), false);
            }
        }

        foreach ($this->rules($config) as $rule) {
            [$name, $arguments] = $this->parseRule($rule);

            if ($name === 'regex' && $type === 'string') {
                $pattern = $this->regexPattern($arguments);
                if ($pattern !== null) {
                    $schema['pattern'] = $pattern;
                }

                continue;
            }

            $parameters = $arguments === '' ? [] : explode(',', $arguments);

            switch ($name) {
                case 'min':
                case 'max':
                    if (isset($parameters[0]) && is_numeric($parameters[0])) {
                        $schema = $this->applyBound($schema, $name, $this->number($parameters[0]), false);
                    }
                    break;
                case 'gte':
                case 'gt':
                    if (isset($parameters[0]) && is_numeric($parameters[0])) {
                        $schema = $this->applyBound($schema, 'min', $this->number($parameters[0]), $name === 'gt');
                    }
                    break;
                case 'lte':
                case 'lt':
                    if (isset($parameters[0]) && is_numeric($parameters[0])) {
                        $schema = $this->applyBound($schema, 'max', $this->number($parameters[0]), $name === 'lt');
                    }
                    break;
                case 'between':
                    if (count($parameters) === 2 && is_numeric($parameters[0]) && is_numeric($parameters[1])) {
                        $schema = $this->applyBound($schema, 'min', $this->number($parameters[0]), false);
                        $schema = $this->applyBound($schema, 'max', $this->number($parameters[1]), false);
                    }
                    break;
                case 'size':
                    if (isset($parameters[0]) && is_numeric($parameters[0])) {
                        $schema = $this->applyBound($schema, 'min', $this->number($parameters[0]), false);
                        $schema = $this->applyBound($schema, 'max', $this->number($parameters[0]), false);
                    }
                    break;
                case 'in':
                    if ($parameters !== [] && ! isset($schema['enum']) && in_array($type, ['string', 'integer', 'number'], true)) {
                        $schema['enum'] = $type === 'string'
                            ? $parameters
                            : array_map(fn (string $value): int|float|string => is_numeric($value) ? $this->number($value) : $value, $parameters);
                    }
                    break;
                case 'distinct':
                    if ($type === 'array') {
                        $schema['uniqueItems'] = true;
                    }
                    break;
                default:
                    if ($type === 'string' && ! isset($schema['format']) && isset(self::FORMAT_RULES[$name])) {
                        $schema['format'] = self::FORMAT_RULES[$name];
                    }
            }
        }

        return $schema;
    }

    /**
     * Apply a lower or upper bound on the keyword matching the schema type.
     *
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    private function applyBound(array $schema, string $bound, int|float $value, bool $exclusive): array
    {
        $type = $schema['type'] ?? null;
        $isMin = $bound === 'min';

        if ($type === 'string' || $type === 'array') {
            // Lengths and item counts are integers; exclusive bounds shift by one
            $count = (int) ($exclusive ? ($isMin ? $value + 1 : $value - 1) : $value);
            $keyword = $type === 'string'
                ? ($isMin ? 'minLength' : 'maxLength')
                : ($isMin ? 'minItems' : 'maxItems');
            $schema[$keyword] = max(0, $count);

            return $schema;
        }

        if ($type !== 'integer' && $type !== 'number') {
            return $schema;
        }

        $keyword = $isMin ? 'minimum' : 'maximum';
        $exclusiveKeyword = $isMin ? 'exclusiveMinimum' : 'exclusiveMaximum';

        if (! $exclusive) {
            $schema[$keyword] = $value;
            unset($schema[$exclusiveKeyword]);

            return $schema;
        }

        if ($this->legacyNullable) {
            $schema[$keyword] = $value;
            $schema[$exclusiveKeyword] = true;
        } else {
            unset($schema[$keyword]);
            $schema[$exclusiveKeyword] = $value;
        }

        return $schema;
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function applyDocumentation(array $schema, array $config): array
    {
        $isReference = isset($schema['$ref']);

        if (isset($config['format']) && is_string($config['format']) && $config['format'] !== '' && ! $isReference) {
            $schema['format'] = $config['format'];
        }

        if ($isReference && (isset($config['description']) || isset($config['example']) || isset($config['default']))) {
            // Sibling keywords next to $ref are ignored in OpenAPI 3.0: wrap the reference
            $schema = ['allOf' => [$schema]];
        }

        if (isset($config['description']) && is_scalar($config['description']) && (string) $config['description'] !== '') {
            $schema['description'] = (string) $config['description'];
        }

        if (array_key_exists('default', $config) && $config['default'] !== null) {
            $schema['default'] = $config['default'];
        }

        if (array_key_exists('example', $config) && $config['example'] !== null) {
            $schema['example'] = $config['example'];
        }

        return $schema;
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    private function makeNullable(array $schema): array
    {
        if ($schema === []) {
            // An untyped schema already accepts null
            return $schema;
        }

        if ($this->legacyNullable) {
            if (isset($schema['$ref'])) {
                $schema = ['allOf' => [$schema]];
            }

            $schema['nullable'] = true;

            return $this->appendNullToEnum($schema);
        }

        if (isset($schema['$ref'])) {
            return ['oneOf' => [$schema, ['type' => 'null']]];
        }

        if (isset($schema['allOf']) && ! isset($schema['type'])) {
            $schema['oneOf'] = [['allOf' => $schema['allOf']], ['type' => 'null']];
            unset($schema['allOf']);

            return $schema;
        }

        if (isset($schema['type']) && is_string($schema['type'])) {
            $schema['type'] = [$schema['type'], 'null'];
        }

        return $this->appendNullToEnum($schema);
    }

    /**
     * Collect the validation rules of a field ("rules" and "validation" keys).
     *
     * @param  array<string, mixed>  $config
     * @return list<string>
     */
    private function rules(array $config): array
    {
        $rules = [];

        foreach (['rules', 'validation'] as $key) {
            $value = $config[$key] ?? [];

            if (is_string($value)) {
                $value = str_contains($value, 'regex:') ? [$value] : explode('|', $value);
            }

            if (! is_array($value)) {
                continue;
            }

            foreach ($value as $rule) {
                if (is_string($rule) && $rule !== '') {
                    $rules[] = $rule;
                }
            }
        }

        return $rules;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parseRule(string $rule): array
    {
        $position = mb_strpos($rule, ':');

        if ($position === false) {
            return [mb_strtolower(mb_trim($rule)), ''];
        }

        return [mb_strtolower(mb_trim(mb_substr($rule, 0, $position))), mb_substr($rule, $position + 1)];
    }

    /**
     * Convert a PHP regular expression (with delimiters) to an ECMA pattern string.
     */
    private function regexPattern(string $regex): ?string
    {
        if (mb_strlen($regex) < 2) {
            return null;
        }

        $delimiter = mb_substr($regex, 0, 1);
        $closing = match ($delimiter) {
            '(' => ')',
            '{' => '}',
            '[' => ']',
            '<' => '>',
            default => $delimiter,
        };

        $end = mb_strrpos($regex, $closing);
        if ($end === false || $end === 0 || ctype_alnum($delimiter) || $delimiter === '\\') {
            return null;
        }

        $pattern = mb_substr($regex, 1, $end - 1);

        return $pattern === '' ? null : $pattern;
    }

    private function number(int|float|string $value): int|float
    {
        if (! is_string($value)) {
            return $value;
        }

        return preg_match('/^[+-]?\d+$/', mb_trim($value)) === 1 ? (int) $value : (float) $value;
    }

    /**
     * Allow null in an enum list (a nullable enum must list null explicitly).
     *
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    private function appendNullToEnum(array $schema): array
    {
        $enum = $schema['enum'] ?? null;

        if (is_array($enum) && ! in_array(null, $enum, true)) {
            $enum[] = null;
            $schema['enum'] = $enum;
        }

        return $schema;
    }
}
