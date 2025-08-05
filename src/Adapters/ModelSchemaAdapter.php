<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Adapters;

use Exception;
use Grazulex\LaravelModelschema\Services\SchemaService;
use Grazulex\LaravelModelschema\Support\FieldTypeRegistry;
use Grazulex\LaravelArc\Services\ModelSchemaFieldTypeMapper;
use Symfony\Component\Yaml\Yaml;

/**
 * ModelSchema Adapter for Laravel Arc
 * 
 * Bridges Laravel Arc DTO generation with grazulex/laravel-modelschema
 * Leverages ModelSchema's powerful field type system (65+ types) and 
 * YamlOptimizationService for enterprise-level performance.
 */
class ModelSchemaAdapter
{
    protected SchemaService $schemaService;
    protected ModelSchemaFieldTypeMapper $typeMapper;

    public function __construct()
    {
        // Initialize minimal services to avoid recursion
        $this->typeMapper = new \Grazulex\LaravelArc\Services\ModelSchemaFieldTypeMapper();
    }

    /**
     * Parse YAML file using ModelSchema capabilities
     * Replaces Symfony\Component\Yaml\Yaml::parseFile() in DtoGenerateCommand
     * 
     * @param string $filePath Path to YAML file
     * @return array Parsed YAML data compatible with Arc's DtoGenerator
     * @throws Exception If YAML parsing fails
     */
    public function parseYamlFile(string $filePath): array
    {
        try {
            // For now, use Symfony YAML directly until we configure Laravel services
            // TODO: Switch to ModelSchema's YamlOptimizationService once Laravel context is available
            $yamlContent = file_get_contents($filePath);
            $parsedData = Yaml::parse($yamlContent);

            // Enhance field types using ModelSchema's FieldTypeRegistry
            return $this->enhanceFieldTypes($parsedData);

        } catch (Exception $e) {
            throw new Exception("Failed to parse YAML file '{$filePath}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Parse YAML file and make it Arc-compatible
     * This method enhances fields with ModelSchema and then transforms them for Arc compatibility
     * 
     * @param string $filePath Path to YAML file
     * @return array Parsed and Arc-compatible YAML data
     * @throws Exception If YAML parsing fails
     */
    public function parseYamlFileForArc(string $filePath): array
    {
        // Use the complete ModelSchema integration service
        $integrationService = new \Grazulex\LaravelArc\Services\ModelSchemaIntegrationService();
        $processedData = $integrationService->processYamlFile($filePath);
        
        // Convert to the format Arc expects
        return [
            'header' => $processedData['header'],
            'fields' => $processedData['processed_fields'], // These are already Arc-compatible
            'relations' => $processedData['relations'],
            'options' => $processedData['options'],
            '_modelschema_complete' => [
                'validation_rules' => $processedData['validation_rules'],
                'cast_types' => $processedData['cast_types'],
                'migration_params' => $processedData['migration_params'],
                'metadata' => $processedData['modelschema_metadata'],
            ],
        ];
    }

    /**
     * Parse multiple YAML documents from a single file
     * Handles multi-document YAML files like Arc's tests/stubs/dto_definitions/multiple-documents-test.yaml
     * 
     * @param string $filePath Path to YAML file
     * @return array Array of parsed YAML documents
     */
    public function parseMultipleDocuments(string $filePath): array
    {
        $yamlContent = file_get_contents($filePath);
        
        // Split YAML documents manually (Symfony YAML approach)
        $documents = [];
        $documentStrings = preg_split('/^---\s*$/m', $yamlContent);
        
        foreach ($documentStrings as $documentString) {
            $documentString = trim($documentString);
            if (!empty($documentString)) {
                $parsedDocument = Yaml::parse($documentString);
                if ($parsedDocument) {
                    $documents[] = $this->enhanceFieldTypes($parsedDocument);
                }
            }
        }
        
        return $documents;
    }

    /**
     * Enhance field types using ModelSchema's powerful FieldTypeRegistry
     * Validates and enriches field definitions with ModelSchema capabilities
     * 
     * @param array $yamlData Parsed YAML data
     * @return array Enhanced YAML data with validated field types
     */
    protected function enhanceFieldTypes(array $yamlData): array
    {
        if (!isset($yamlData['fields']) || !is_array($yamlData['fields'])) {
            return $yamlData;
        }

        foreach ($yamlData['fields'] as $fieldName => &$fieldConfig) {
            if (!isset($fieldConfig['type'])) {
                continue;
            }

            $fieldType = $fieldConfig['type'];

            // Handle Arc legacy types that don't exist in ModelSchema
            if ($fieldType === 'array') {
                $fieldType = 'json'; // ModelSchema uses 'json' for arrays
                $fieldConfig['type'] = 'json';
                $fieldConfig['_arc_original_type'] = 'array'; // Keep original for compatibility
            }

            // Validate field type using ModelSchema's FieldTypeRegistry
            if (!FieldTypeRegistry::has($fieldType)) {
                // Log warning but don't fail - maintain backward compatibility
                error_log("Warning: Unknown field type '{$fieldType}' for field '{$fieldName}'. Available types: " . 
                         implode(', ', array_slice(FieldTypeRegistry::all(), 0, 10)) . '...');
                continue;
            }

            // Get field type instance for enhanced capabilities
            try {
                $fieldTypeInstance = FieldTypeRegistry::get($fieldType);
                
                // Add ModelSchema metadata to field config
                $fieldConfig['_modelschema'] = [
                    'type_class' => get_class($fieldTypeInstance),
                    'migration_parameters' => $fieldTypeInstance->getMigrationParameters($fieldConfig),
                    'cast_type' => $fieldTypeInstance->getCastType($fieldConfig),
                    'aliases' => $fieldTypeInstance->getAliases(),
                    'validation_rules' => $fieldTypeInstance->getValidationRules($fieldConfig),
                ];
            } catch (Exception $e) {
                // Log error but continue
                error_log("Error enhancing field type '{$fieldType}': " . $e->getMessage());
            }
        }

        return $yamlData;
    }

    /**
     * Get available field types from ModelSchema
     * Provides access to ModelSchema's 65+ field types for Arc users
     * 
     * @return array List of available field types
     */
    public function getAvailableFieldTypes(): array
    {
        return FieldTypeRegistry::all();
    }

    /**
     * Check if a field type is supported by ModelSchema
     * 
     * @param string $type Field type to check
     * @return bool True if supported
     */
    public function isFieldTypeSupported(string $type): bool
    {
        return FieldTypeRegistry::has($type);
    }

    /**
     * Get field type aliases (e.g., 'varchar' -> 'string', 'int' -> 'integer')
     * 
     * @param string $type Field type or alias
     * @return string Canonical field type
     */
    public function resolveFieldTypeAlias(string $type): string
    {
        if (!FieldTypeRegistry::has($type)) {
            return $type;
        }

        try {
            $fieldTypeInstance = FieldTypeRegistry::get($type);
            return $fieldTypeInstance->getType();
        } catch (Exception $e) {
            return $type;
        }
    }

    /**
     * Get statistics about ModelSchema capabilities
     * Useful for debugging and monitoring
     * 
     * @return array Statistics
     */
    public function getStatistics(): array
    {
        $types = FieldTypeRegistry::all();
        $baseTypes = FieldTypeRegistry::getBaseTypes();
        
        return [
            'total_field_types' => count($types),
            'base_field_types' => count($baseTypes),
            'aliases_count' => count($types) - count($baseTypes),
            'sample_types' => array_slice($types, 0, 10),
            'geometric_types' => array_filter($types, function($type) {
                return in_array($type, ['point', 'geometry', 'polygon', 'geopoint', 'coordinates']);
            }),
        ];
    }
}
