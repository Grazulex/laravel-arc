<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Services;

use Symfony\Component\Yaml\Yaml;

/**
 * Minimal ModelSchema integration service that only does type mapping.
 * Avoids Laravel container and FieldTypeRegistry to prevent recursion.
 */
class MinimalModelSchemaIntegrationService
{
    private ModelSchemaFieldTypeMapper $typeMapper;
    
    public function __construct()
    {
        $this->typeMapper = new ModelSchemaFieldTypeMapper();
    }
    
    /**
     * Process YAML file with minimal ModelSchema integration.
     */
    public function processYamlFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("YAML file not found: {$filePath}");
        }
        
        $yaml = Yaml::parseFile($filePath);
        
        // Process fields with type mapping only
        $processedFields = [];
        foreach ($yaml['fields'] ?? [] as $name => $field) {
            $processedFields[$name] = $this->processField($name, $field);
        }
        
        return [
            'header' => $yaml['header'] ?? [],
            'fields' => $processedFields, // Fix: was 'processed_fields'
            'relations' => $yaml['relations'] ?? [],
            'options' => $yaml['options'] ?? [],
        ];
    }
    
    /**
     * Process a field with basic type mapping only.
     */
    private function processField(string $name, array $field): array
    {
        $originalType = $field['type'] ?? 'string';
        
        // Map to Arc-compatible type
        $arcType = $this->typeMapper->mapToArcType($originalType);
        $field['type'] = $arcType;
        
        // Add minimal ModelSchema metadata
        $field['_modelschema'] = [
            'original_type' => $originalType,
            'mapped_to' => $arcType,
        ];
        
        // Handle nullable fields properly - check if already nullable or should be
        if (!isset($field['nullable']) && in_array($originalType, ['point', 'polygon', 'geometry', 'json', 'set'])) {
            $field['nullable'] = true;
        }
        
        return $field;
    }
    
    /**
     * Get basic integration statistics.
     */
    public function getIntegrationStatistics(): array
    {
        $mappings = $this->typeMapper->getAllMappings();
        
        // Count different categories
        $geometricTypes = ['point', 'polygon', 'geometry', 'linestring', 'multipoint', 'multipolygon', 'multilinestring', 'geometrycollection'];
        $jsonTypes = ['json', 'jsonb', 'set', 'array', 'collection'];
        $stringTypes = ['email', 'uuid', 'url', 'slug', 'phone', 'color', 'ip', 'ipv4', 'ipv6', 'mac', 'currency', 'locale', 'timezone'];
        
        return [
            'field_types_available' => count($mappings),
            'geometric_types' => count(array_intersect(array_keys($mappings), $geometricTypes)),
            'json_types' => count(array_intersect(array_keys($mappings), $jsonTypes)),
            'enhanced_string_types' => count(array_intersect(array_keys($mappings), $stringTypes)),
            'integration_status' => 'minimal_integration',
            'type_mapping_coverage' => 'comprehensive',
        ];
    }
}
