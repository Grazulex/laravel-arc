<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Services;

/**
 * Service to map ModelSchema field types to Arc field generators
 */
class ModelSchemaFieldTypeMapper
{
    /**
     * Mapping from ModelSchema types to Arc-compatible types
     */
    protected array $typeMappings = [
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
        'datetime' => 'datetime',
        'timestamp' => 'datetime',
        'date' => 'date',
        'time' => 'time',
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
        'softdeletes' => 'datetime',
        'timestamps' => 'datetime',
        
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
