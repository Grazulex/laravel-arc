<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Services;

use Grazulex\LaravelModelschema\Schema\ModelSchema;

/**
 * Advanced ModelSchema integration service that delegates EVERYTHING to ModelSchema.
 * Arc becomes just an executor - ModelSchema is the real boss!
 */
final class AdvancedModelSchemaIntegrationService
{
    /**
     * Process YAML file with full ModelSchema power.
     * ModelSchema does ALL the work, Arc just executes orders!
     */
    public function processYamlFile(string $filePath): array
    {
        // 🎯 ModelSchema fait TOUT le travail - parsing direct sans cache
        $modelSchema = ModelSchema::fromYamlFile($filePath);

        // 🚀 Extractons les données ready-to-use pour Arc
        return $this->extractArcCompatibleData($modelSchema);
    }

    /**
     * Get ModelSchema processing statistics.
     * Shows the real power we're delegating to ModelSchema!
     */
    public function getIntegrationStatistics(): array
    {
        // Créer un ModelSchema temporaire pour obtenir les stats
        $tempSchema = ModelSchema::fromArray('TempSchema', [
            'fields' => [
                'test_point' => ['type' => 'point'],
                'test_json' => ['type' => 'json'],
                'test_email' => ['type' => 'email'],
            ],
        ]);

        $allFields = $tempSchema->getAllFields();
        $validationRules = $tempSchema->getValidationRules();
        $castableFields = $tempSchema->getCastableFields();

        return [
            'integration_type' => 'advanced_modelschema',
            'delegation_status' => 'modelschema_is_boss',
            'arc_role' => 'executor_only',
            'field_processing' => 'delegated_to_modelschema',
            'validation_rules' => 'generated_by_modelschema',
            'cast_types' => 'determined_by_modelschema',
            'sample_validation_rules' => count($validationRules),
            'sample_cast_types' => count($castableFields),
            'sample_fields_processed' => count($allFields),
        ];
    }

    /**
     * Get full ModelSchema capabilities for debugging.
     */
    public function getModelSchemaCapabilities(): array
    {
        return [
            'class' => ModelSchema::class,
            'features' => [
                'parseYamlFile' => 'Parse YAML to ModelSchema',
                'getAllFields' => 'Get all fields with relationships',
                'getValidationRules' => 'Generate Laravel validation rules',
                'getCastableFields' => 'Determine Laravel cast types',
                'getFillableFields' => 'Determine fillable attributes',
                'hasTimestamps' => 'Check if model uses timestamps',
                'hasSoftDeletes' => 'Check if model uses soft deletes',
            ],
            'delegation_benefits' => [
                'No more manual type mapping',
                'Validation rules come from ModelSchema',
                'Cast types come from ModelSchema',
                'Field processing comes from ModelSchema',
                'Arc just generates PHP code',
            ],
        ];
    }

    /**
     * Extract Arc-compatible data from ModelSchema.
     * ModelSchema tells Arc exactly what to generate!
     */
    private function extractArcCompatibleData(ModelSchema $modelSchema): array
    {
        $arcFields = [];

        // 🎯 ModelSchema nous donne TOUS les champs processés
        foreach ($modelSchema->getAllFields() as $field) {
            $arcFields[$field->name] = [
                // ✅ ModelSchema nous dit le type PHP à utiliser
                'type' => $this->getArcTypeFromField($field),

                // ✅ ModelSchema nous donne la nullabilité
                'nullable' => $field->nullable,

                // ✅ ModelSchema nous donne les validation rules
                'validation' => $field->getValidationRules(),

                // ✅ ModelSchema nous donne le cast type Laravel
                'cast_type' => $field->getCastType(),

                // ✅ Métadonnées complètes depuis ModelSchema
                '_modelschema' => [
                    'original_type' => $field->type,
                    'is_fillable' => $field->isFillable(),
                    'validation_rules' => $field->getValidationRules(),
                    'cast_type' => $field->getCastType(),
                    'comment' => $field->comment,
                    'attributes' => $field->attributes,
                ],
            ];
        }

        return [
            'header' => [
                'class' => $modelSchema->name.'DTO',  // Arc attend 'class', pas 'class_name'
                'namespace' => 'App\\DTOs',
                'table' => $modelSchema->table,
            ],
            'fields' => $arcFields,
            'relations' => [], // TODO: Relations depuis ModelSchema
            'options' => $modelSchema->options,
        ];
    }

    /**
     * Get Arc-compatible type from ModelSchema Field.
     * ModelSchema knows best, we just translate to Arc's limited types.
     */
    private function getArcTypeFromField($field): string
    {
        // 🎯 ModelSchema nous dit quel type PHP utiliser
        $castType = $field->getCastType();

        // Mapping intelligent basé sur le cast type de ModelSchema
        return match ($castType) {
            'boolean' => 'bool',
            'integer' => 'int',
            'array' => 'array',
            'datetime', 'date' => 'string', // DTO uses string for dates
            null => match ($field->type) {
                // Types géométriques → string pour DTO
                'point', 'polygon', 'geometry', 'linestring' => 'string',
                // Types JSON → array pour DTO
                'json', 'jsonb', 'set' => 'array',
                // Types enhanced string → string pour DTO
                'email', 'uuid', 'url', 'slug' => 'string',
                // Fallback
                default => 'string',
            },
            default => 'string', // Fallback sécurisé
        };
    }
}
