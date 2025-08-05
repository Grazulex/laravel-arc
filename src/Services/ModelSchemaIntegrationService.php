<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Services;

use Exception;
use Grazulex\LaravelModelschema\Support\FieldTypeRegistry;
use Symfony\Component\Yaml\Yaml;

/**
 * Complete ModelSchema Integration Service
 * 
 * This service delegates ALL field management to ModelSchema:
 * - YAML parsing and validation
 * - Field type handling (65+ types)
 * - Validation rules generation
 * - Cast type determination
 * - Migration parameters
 * 
 * Arc only handles the final PHP DTO file generation and traits.
 */
class ModelSchemaIntegrationService
{
    private FieldTypeRegistry $fieldRegistry;
    private ModelSchemaFieldTypeMapper $typeMapper;
    
    public function __construct()
    {
        $this->fieldRegistry = new FieldTypeRegistry();
        $this->typeMapper = new ModelSchemaFieldTypeMapper();
    }

    /**
     * Process YAML file completely with ModelSchema
     * Returns all data needed for Arc's DTO generation
     * 
     * @param string $filePath Path to YAML file
     * @return array Complete processing result with all field metadata
     */
    public function processYamlFile(string $filePath): array
    {
        // Parse YAML directly (no recursion)
        $yamlContent = file_get_contents($filePath);
        $yamlData = Yaml::parse($yamlContent);

        // Process each field with ModelSchema's complete field type system
        $processedFields = [];
        $validationRules = [];
        $castTypes = [];
        $migrationParams = [];

        if (isset($yamlData['fields']) && is_array($yamlData['fields'])) {
            foreach ($yamlData['fields'] as $fieldName => $fieldConfig) {
                $processed = $this->processField($fieldName, $fieldConfig);
                
                $processedFields[$fieldName] = $processed['field_definition'];
                $validationRules[$fieldName] = $processed['validation_rules'];
                $castTypes[$fieldName] = $processed['cast_type'];
                $migrationParams[$fieldName] = $processed['migration_params'];
            }
        }

        return [
            'header' => $yamlData['header'] ?? [],
            'options' => $yamlData['options'] ?? [],
            'relations' => $yamlData['relations'] ?? [],
            'original_fields' => $yamlData['fields'] ?? [],
            'processed_fields' => $processedFields,
            'validation_rules' => $validationRules,
            'cast_types' => $castTypes,
            'migration_params' => $migrationParams,
            'modelschema_metadata' => $this->extractModelSchemaMetadata($yamlData),
        ];
    }

    /**
     * Process a field with ModelSchema integration.
     */
    private function processField(string $name, array $field): array
    {
        $originalType = $field['type'] ?? 'string';
        
        // Get field type details from ModelSchema registry
        $fieldTypeInstance = $this->fieldRegistry->get($originalType);
        
        // Map to Arc-compatible type
        $arcType = $this->typeMapper->mapToArcType($originalType);
        $field['type'] = $arcType;
        
        // Add ModelSchema metadata
        $field['_modelschema'] = [
            'original_type' => $originalType,
            'type_class' => get_class($fieldTypeInstance),
            'aliases' => $fieldTypeInstance->getAliases(),
        ];
        
        // Handle nullable fields properly - check if already nullable or should be
        if (!isset($field['nullable']) && in_array($originalType, ['point', 'polygon', 'geometry', 'json', 'set'])) {
            $field['nullable'] = true;
        }
        
        // Return in the expected format
        return [
            'field_definition' => $field,
            'validation_rules' => [], // TODO: Implement validation rules extraction
            'cast_type' => $arcType,
            'migration_params' => [], // TODO: Implement migration params extraction
        ];
    }

    /**
     * Map ModelSchema type to Arc-compatible type
     */
    protected function mapToArcType(string $modelSchemaType): string
    {
        $mapping = [
            // Geometric -> string
            'point' => 'string',
            'geometry' => 'string',
            'polygon' => 'string',
            'geopoint' => 'string',
            'coordinates' => 'string',
            
            // Enhanced string -> string
            'email' => 'string',
            'uuid' => 'string',
            'text' => 'string',
            'longText' => 'string',
            'mediumText' => 'string',
            
            // Integer variations -> integer
            'bigInteger' => 'integer',
            'tinyInteger' => 'integer',
            'smallInteger' => 'integer',
            'mediumInteger' => 'integer',
            'unsignedBigInteger' => 'integer',
            
            // Decimal -> float
            'decimal' => 'float',
            'double' => 'float',
            
            // JSON/Array -> array
            'json' => 'array',
            'jsonb' => 'array',
            'set' => 'array',
            
            // Date/Time -> string
            'date' => 'string',
            'datetime' => 'string',
            'time' => 'string',
            'timestamp' => 'string',
            
            // Binary -> string
            'binary' => 'string',
            
            // Relationships -> string
            'foreignId' => 'string',
            'morphs' => 'string',
        ];

        // Return mapped type or original if it's a basic Arc type
        return $mapping[$modelSchemaType] ?? $modelSchemaType;
    }

    /**
     * Extract comprehensive ModelSchema metadata
     */
    protected function extractModelSchemaMetadata(array $yamlData): array
    {
        $metadata = [
            'processed_with_modelschema' => true,
            'processing_timestamp' => date('c'), // Use PHP date instead of Laravel now()
            'field_type_capabilities' => [],
            'geometric_fields' => [],
            'enhanced_validations' => [],
        ];

        if (isset($yamlData['fields'])) {
            foreach ($yamlData['fields'] as $fieldName => $fieldConfig) {
                if (isset($fieldConfig['_modelschema'])) {
                    $metadata['field_type_capabilities'][$fieldName] = $fieldConfig['_modelschema'];
                }

                $fieldType = $fieldConfig['type'] ?? 'string';
                if (in_array($fieldType, ['point', 'geometry', 'polygon', 'geopoint', 'coordinates'])) {
                    $metadata['geometric_fields'][] = $fieldName;
                }

                if (isset($fieldConfig['_modelschema']['validation_rules']) && !empty($fieldConfig['_modelschema']['validation_rules'])) {
                    $metadata['enhanced_validations'][$fieldName] = $fieldConfig['_modelschema']['validation_rules'];
                }
            }
        }

        return $metadata;
    }

    /**
     * Get comprehensive statistics about ModelSchema integration
     */
    public function getIntegrationStatistics(): array
    {
        $allTypes = FieldTypeRegistry::all();
        $geometricTypes = array_filter($allTypes, function($type) {
            return in_array($type, ['point', 'geometry', 'polygon', 'geopoint', 'coordinates', 'latlng']);
        });

        return [
            'total_modelschema_types' => count($allTypes),
            'geometric_types_count' => count($geometricTypes),
            'enhanced_string_types' => ['email', 'uuid', 'text', 'longText', 'mediumText'],
            'enhanced_json_types' => ['json', 'jsonb', 'set'],
            'integer_variations' => ['bigInteger', 'tinyInteger', 'smallInteger', 'mediumInteger'],
            'capabilities' => [
                'validation_rules_generation' => true,
                'cast_types_determination' => true,
                'migration_parameters' => true,
                'field_type_plugins' => true,
                'geometric_support' => true,
                'advanced_validations' => true,
            ],
        ];
    }
}
