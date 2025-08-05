<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerator;
use Grazulex\LaravelArc\Services\AdvancedModelSchemaIntegrationService;

it('can use ModelSchema as the real boss for DTO generation', function () {
    // 🎯 Test : ModelSchema fait TOUT, Arc obéit

    $yamlContent = <<<'YAML'
model: Restaurant
table: restaurants
fields:
  id:
    type: integer
    nullable: false
  name:
    type: string
    length: 255
    nullable: false
  location:
    type: point
    nullable: true
    comment: "GPS coordinates"
  opening_hours:
    type: json
    nullable: true
    comment: "Weekly schedule"
  cuisine_tags:
    type: set
    nullable: true
    comment: "Cuisine types"
  contact_email:
    type: email
    nullable: false
  rating:
    type: decimal
    precision: 3
    scale: 2
    nullable: true
  is_verified:
    type: boolean
    nullable: false
    default: false
YAML;

    $yamlFile = '/tmp/restaurant-model-schema.yaml';
    file_put_contents($yamlFile, $yamlContent);

    // 🚀 Test du service d'intégration avancé
    $service = new AdvancedModelSchemaIntegrationService();
    $processedData = $service->processYamlFile($yamlFile);

    // ✅ Vérifier que ModelSchema nous donne tout ce qu'il faut
    expect($processedData)
        ->toHaveKey('header')
        ->toHaveKey('fields')
        ->toHaveKey('relations')
        ->toHaveKey('options');

    // ✅ Vérifier les champs processés par ModelSchema
    $fields = $processedData['fields'];

    // Geometric field : ModelSchema dit string pour DTO
    expect($fields['location'])
        ->toHaveKey('type', 'string')
        ->toHaveKey('nullable', true)
        ->toHaveKey('_modelschema.original_type', 'point');

    // JSON field : ModelSchema dit array pour DTO
    expect($fields['opening_hours'])
        ->toHaveKey('type', 'array')
        ->toHaveKey('cast_type', 'array')
        ->toHaveKey('_modelschema.original_type', 'json');

    // Set field : ModelSchema dit array pour DTO
    expect($fields['cuisine_tags'])
        ->toHaveKey('type', 'array')
        ->toHaveKey('_modelschema.original_type', 'set');

    // Email field : ModelSchema dit string pour DTO + validation
    expect($fields['contact_email'])
        ->toHaveKey('type', 'string')
        ->toHaveKey('_modelschema.original_type', 'email');

    // Boolean field : ModelSchema dit bool pour DTO
    expect($fields['is_verified'])
        ->toHaveKey('type', 'bool')
        ->toHaveKey('cast_type', 'boolean')
        ->toHaveKey('_modelschema.original_type', 'boolean');

    // Decimal field : ModelSchema dit string pour DTO (précision préservée)
    expect($fields['rating'])
        ->toHaveKey('type', 'string')
        ->toHaveKey('cast_type', 'decimal:2')
        ->toHaveKey('_modelschema.original_type', 'decimal');

    // ✅ Vérifier que ModelSchema fournit validation rules
    foreach ($fields as $field) {
        expect($field)
            ->toHaveKey('validation')
            ->toHaveKey('_modelschema.validation_rules');
    }

    // 🎯 Test génération DTO complète avec ModelSchema boss
    $generator = DtoGenerator::make();
    $dtoCode = $generator->generateFromDefinition([], $yamlFile);

    // ✅ Vérifier le DTO généré
    expect($dtoCode)
        ->toContain('final class RestaurantDTO')
        ->toContain('public readonly string $location')     // point → string
        ->toContain('public readonly array $opening_hours') // json → array
        ->toContain('public readonly array $cuisine_tags')  // set → array
        ->toContain('public readonly string $contact_email') // email → string
        ->toContain('public readonly bool $is_verified')    // boolean → bool
        ->toContain('public readonly string $rating');      // decimal → string

    // 🚀 Test des statistiques d'intégration
    $stats = $service->getIntegrationStatistics();

    expect($stats)
        ->toHaveKey('integration_type', 'advanced_modelschema')
        ->toHaveKey('delegation_status', 'modelschema_is_boss')
        ->toHaveKey('arc_role', 'executor_only')
        ->toHaveKey('field_processing', 'delegated_to_modelschema');

    // ✅ Test des capacités ModelSchema
    $capabilities = $service->getModelSchemaCapabilities();

    expect($capabilities)
        ->toHaveKey('features')
        ->toHaveKey('delegation_benefits');

    // Nettoyer
    unlink($yamlFile);
});

it('shows ModelSchema delegation benefits in statistics', function () {
    $service = new AdvancedModelSchemaIntegrationService();
    $stats = $service->getIntegrationStatistics();

    // ✅ Vérifier que les stats montrent la délégation
    expect($stats['integration_type'])->toBe('advanced_modelschema');
    expect($stats['delegation_status'])->toBe('modelschema_is_boss');
    expect($stats['arc_role'])->toBe('executor_only');

    $capabilities = $service->getModelSchemaCapabilities();

    // ✅ Vérifier les bénéfices de la délégation
    expect($capabilities['delegation_benefits'])
        ->toContain('No more manual type mapping')
        ->toContain('Validation rules come from ModelSchema')
        ->toContain('Cast types come from ModelSchema')
        ->toContain('Arc just generates PHP code');
});
