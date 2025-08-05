<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Services;

/**
 * Service to map ModelSchema field types to Arc field generators
 */
final class ModelSchemaFieldTypeMapper
{
    /**
     * Mapping from ModelSchema types to Arc-compatible types
     */
    private array $typeMappings = [
        // === Geometric Types ===
        'point' => 'string',
        'geometry' => 'string',
        'polygon' => 'string',
        'linestring' => 'string',
        'multipoint' => 'string',
        'multipolygon' => 'string',
        'multilinestring' => 'string',
        'geometrycollection' => 'string',

        // === Enhanced String Types ===
        'email' => 'string',
        'uuid' => 'string',
        'url' => 'string',
        'slug' => 'string',
        'phone' => 'string',
        'color' => 'string',
        'ip' => 'string',
        'ipv4' => 'string',
        'ipv6' => 'string',
        'mac' => 'string',
        'currency' => 'string',
        'locale' => 'string',
        'timezone' => 'string',

        // === JSON and Array Types ===
        'json' => 'array',
        'jsonb' => 'array',
        'set' => 'array',
        'array' => 'array',
        'collection' => 'array',

        // === Numeric Types ===
        'float' => 'decimal',
        'double' => 'decimal',
        'decimal' => 'decimal',
        'money' => 'decimal',
        'int' => 'integer',
        'bigint' => 'integer',
        'smallint' => 'integer',
        'tinyint' => 'integer',
        'unsignedint' => 'integer',
        'bool' => 'boolean',
        'boolean' => 'boolean',

        // === Date/Time Types ===
        'datetime' => 'string',    // Changed from 'datetime' to 'string' for ModelSchema
        'timestamp' => 'string',   // Changed from 'datetime' to 'string' for ModelSchema
        'date' => 'string',        // Changed from 'date' to 'string' for ModelSchema
        'time' => 'string',        // Changed from 'time' to 'string' for ModelSchema
        'year' => 'integer',

        // === Text Types ===
        'text' => 'text',
        'longtext' => 'text',
        'mediumtext' => 'text',
        'varchar' => 'string',
        'char' => 'string',
        'string' => 'string',

        // === Binary Types ===
        'binary' => 'string',
        'blob' => 'string',
        'longblob' => 'string',
        'mediumblob' => 'string',

        // === Special Types ===
        'enum' => 'string',
        'morphs' => 'string',
        'nullablemorphs' => 'string',
        'uuidmorphs' => 'string',
        'nullableuuidmorphs' => 'string',
        'remembertokens' => 'string',
        'softdeletes' => 'string',    // Changed from 'datetime' to 'string' for consistency
        'timestamps' => 'string',     // Changed from 'datetime' to 'string' for consistency

        // === Fulltext and Search ===
        'fulltext' => 'string',
        'spatialindex' => 'string',
    ];

    /**
     * Map a ModelSchema field type to an Arc-compatible type.
     */
    public function mapToArcType(string $modelSchemaType): string
    {
        return $this->typeMappings[$modelSchemaType] ?? $modelSchemaType;
    }

    /**
     * Get all type mappings.
     */
    public function getAllMappings(): array
    {
        return $this->typeMappings;
    }
}
