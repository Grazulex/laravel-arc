<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Export\OpenApi;

use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;
use InvalidArgumentException;
use stdClass;
use Symfony\Component\Yaml\Yaml;

/**
 * Generates OpenAPI schemas (components/schemas) from YAML DTO definitions.
 *
 * Each definition becomes one object schema named after the DTO class short name.
 * Nested DTOs and relations are emitted as references to "#/components/schemas/{Name}".
 */
final class OpenApiSchemaGenerator
{
    /** Keys whose value is a map and must be encoded as an object even when empty. */
    private const OBJECT_KEYS = ['paths', 'components', 'schemas', 'properties', 'items', 'additionalProperties'];

    /** Keys whose value maps names to schemas. */
    private const SCHEMA_MAP_KEYS = ['schemas', 'properties'];

    /** Keys holding literal values that must not be rewritten. */
    private const LITERAL_KEYS = ['default', 'example', 'enum'];

    private string $openApiVersion;

    private OpenApiFieldMapper $mapper;

    /**
     * @param  string  $openApiVersion  "3.0", "3.1" or a full version such as "3.0.3" / "3.1.0"
     */
    public function __construct(string $openApiVersion = '3.1.0', ?OpenApiFieldMapper $mapper = null)
    {
        $this->openApiVersion = match ($openApiVersion) {
            '3.0' => '3.0.3',
            '3.1' => '3.1.0',
            default => $openApiVersion,
        };

        if (preg_match('/^3\.[01]\.\d+$/', $this->openApiVersion) !== 1) {
            throw new InvalidArgumentException("Unsupported OpenAPI version: {$openApiVersion} (supported: 3.0, 3.1)");
        }

        $this->mapper = $mapper ?? new OpenApiFieldMapper($this->openApiVersion);
    }

    public function openApiVersion(): string
    {
        return $this->openApiVersion;
    }

    /**
     * Resolve the schema name of a definition (the DTO class short name).
     *
     * @param  array<string, mixed>  $definition
     */
    public function schemaName(array $definition): string
    {
        $header = is_array($definition['header'] ?? null) ? $definition['header'] : [];
        $name = $header['dto'] ?? $header['class'] ?? $definition['class'] ?? null;

        if (! is_string($name) || $name === '') {
            throw new InvalidArgumentException('The definition has no DTO name (header.dto or header.class).');
        }

        return OpenApiFieldMapper::shortName($name);
    }

    /**
     * Generate the object schema of a single definition.
     *
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    public function generateSchema(array $definition): array
    {
        $header = is_array($definition['header'] ?? null) ? $definition['header'] : [];

        $schema = ['type' => 'object'];

        $description = $header['description'] ?? $definition['description'] ?? null;
        if (is_string($description) && $description !== '') {
            $schema['description'] = $description;
        }

        $properties = [];
        $required = [];

        foreach ($this->resolveFields($definition) as $name => $config) {
            $name = (string) $name;
            $config = is_array($config) ? $config : ['type' => (string) $config];
            $properties[$name] = $this->mapper->map($config);

            if ($this->mapper->isRequired($config)) {
                $required[] = $name;
            }
        }

        $relations = is_array($definition['relations'] ?? null) ? $definition['relations'] : [];
        foreach ($relations as $name => $relation) {
            $name = (string) $name;
            if (! is_array($relation) || isset($properties[$name])) {
                continue;
            }

            $properties[$name] = $this->mapper->mapRelation($relation);

            if ($this->mapper->isRequired($relation)) {
                $required[] = $name;
            }
        }

        $schema['properties'] = $properties;

        if ($required !== []) {
            $schema['required'] = $required;
        }

        return $schema;
    }

    /**
     * Generate the "components" object for a list of definitions.
     *
     * @param  iterable<array<string, mixed>>  $definitions
     * @return array{schemas: array<string, array<string, mixed>>}
     */
    public function generateComponents(iterable $definitions): array
    {
        $schemas = [];

        foreach ($definitions as $definition) {
            $name = $this->schemaName($definition);

            if (isset($schemas[$name])) {
                throw new InvalidArgumentException("Duplicate OpenAPI schema name: {$name}");
            }

            $schemas[$name] = $this->generateSchema($definition);
        }

        ksort($schemas);

        return ['schemas' => $schemas];
    }

    /**
     * Generate a complete OpenAPI document holding the DTO schemas under components/schemas.
     *
     * Options:
     *  - title: info.title (default "DTO Schemas")
     *  - version: info.version (default "1.0.0")
     *  - description: info.description (optional)
     *
     * @param  iterable<array<string, mixed>>  $definitions
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function generateDocument(iterable $definitions, array $options = []): array
    {
        $info = [
            'title' => (string) ($options['title'] ?? 'DTO Schemas'),
            'version' => (string) ($options['version'] ?? '1.0.0'),
        ];

        if (isset($options['description']) && is_string($options['description']) && $options['description'] !== '') {
            $info['description'] = $options['description'];
        }

        return [
            'openapi' => $this->openApiVersion,
            'info' => $info,
            'paths' => [],
            'components' => $this->generateComponents($definitions),
        ];
    }

    /**
     * List the schema names referenced through $ref but missing from components/schemas.
     *
     * @param  array<string, mixed>  $document  A document or a components array
     * @return list<string>
     */
    public function missingReferences(array $document): array
    {
        $components = is_array($document['components'] ?? null) ? $document['components'] : $document;
        $schemas = is_array($components['schemas'] ?? null) ? $components['schemas'] : [];

        $references = [];
        array_walk_recursive($schemas, function (mixed $value, int|string $key) use (&$references): void {
            if ($key === '$ref' && is_string($value) && str_starts_with($value, OpenApiFieldMapper::DEFAULT_REF_PREFIX)) {
                $references[mb_substr($value, mb_strlen(OpenApiFieldMapper::DEFAULT_REF_PREFIX))] = true;
            }
        });

        $missing = array_values(array_diff(array_map('strval', array_keys($references)), array_map('strval', array_keys($schemas))));
        sort($missing);

        return $missing;
    }

    /**
     * Encode a document (or any schema array) as pretty-printed JSON.
     *
     * @param  array<string, mixed>  $document
     */
    public function toJson(array $document): string
    {
        return json_encode(
            self::normalize($document, true),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR
        )."\n";
    }

    /**
     * Encode a document (or any schema array) as YAML.
     *
     * @param  array<string, mixed>  $document
     */
    public function toYaml(array $document): string
    {
        return Yaml::dump(
            self::normalize($document, true),
            20,
            2,
            Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
        );
    }

    /**
     * Turn the empty maps of a document (properties, items, paths, untyped schemas...) into
     * objects so they are encoded as {} instead of []. Literal values (default, example, enum)
     * are kept as they are.
     */
    private static function normalize(mixed $value, bool $asObject = false, bool $isSchemaMap = false): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if ($value === []) {
            return $asObject ? new stdClass() : [];
        }

        $result = [];
        foreach ($value as $key => $item) {
            if (! $isSchemaMap && in_array($key, self::LITERAL_KEYS, true)) {
                $result[$key] = $item;

                continue;
            }

            $result[$key] = self::normalize(
                $item,
                $isSchemaMap || in_array($key, self::OBJECT_KEYS, true),
                ! $isSchemaMap && in_array($key, self::SCHEMA_MAP_KEYS, true)
            );
        }

        return $result;
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
}
