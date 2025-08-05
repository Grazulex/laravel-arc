<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Adapters\ModelSchemaAdapter;
use Grazulex\LaravelArc\Generator\DtoGenerator;
use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;

describe('ModelSchema Advanced Features Integration', function () {
    beforeEach(function () {
        // Ensure traits are registered
        BehavioralTraitRegistry::registerDefaults();
        $this->adapter = new ModelSchemaAdapter();
    });

    it('can parse and enhance YAML with advanced ModelSchema field types', function () {
        $yamlFile = __DIR__.'/DtoGenerator/fixtures/advanced-modelschema.yaml';
        $yaml = $this->adapter->parseYamlFile($yamlFile);

        // Verify basic structure
        expect($yaml)->toHaveKey('header');
        expect($yaml)->toHaveKey('fields');
        expect($yaml['header']['dto'])->toBe('AdvancedLocationDTO');

        // Verify advanced field types are recognized
        expect($yaml['fields']['coordinates']['type'])->toBe('point');
        expect($yaml['fields']['boundary']['type'])->toBe('polygon');
        expect($yaml['fields']['email']['type'])->toBe('email');
        expect($yaml['fields']['metadata']['type'])->toBe('json');
        expect($yaml['fields']['tags']['type'])->toBe('set');

        // Verify ModelSchema metadata is added
        expect($yaml['fields']['coordinates'])->toHaveKey('_modelschema');
        expect($yaml['fields']['boundary'])->toHaveKey('_modelschema');
        expect($yaml['fields']['email'])->toHaveKey('_modelschema');

        // Verify specific ModelSchema enhancements
        $coordinatesMetadata = $yaml['fields']['coordinates']['_modelschema'];
        expect($coordinatesMetadata)->toHaveKey('type_class');
        expect($coordinatesMetadata)->toHaveKey('migration_parameters');
        expect($coordinatesMetadata)->toHaveKey('cast_type');
        expect($coordinatesMetadata)->toHaveKey('aliases');

        // Check if geometric aliases are available
        expect($coordinatesMetadata['aliases'])->toContain('geopoint');
        expect($coordinatesMetadata['aliases'])->toContain('coordinates');
    });

    it('can generate DTO code with advanced field types', function () {
        $yamlFile = __DIR__.'/DtoGenerator/fixtures/advanced-modelschema.yaml';

        // Use the minimal integration service to avoid recursion
        $integrationService = new Grazulex\LaravelArc\Services\MinimalModelSchemaIntegrationService();
        $processedData = $integrationService->processYamlFile($yamlFile);

        // Convert to Arc format
        $yaml = [
            'header' => $processedData['header'],
            'fields' => $processedData['fields'],  // ✅ Corrigé : 'fields' au lieu de 'processed_fields'
            'relations' => $processedData['relations'],
            'options' => $processedData['options'],
        ];

        $code = DtoGenerator::make()->generateFromDefinition($yaml);

        expect($code)
            ->toContain('final class AdvancedLocationDTO')
            ->toContain('public readonly string $id')
            ->toContain('public readonly string $name')
            ->toContain('public readonly string $coordinates')  // Point type becomes string (non-nullable)
            ->toContain('public readonly string $boundary')     // Polygon type becomes string (non-nullable)
            ->toContain('public readonly string $email')
            ->toContain('public readonly string $website')
            ->toContain('public readonly array $metadata')     // JSON type becomes array (non-nullable)
            ->toContain('public readonly string $price')        // Price as string
            ->toContain('public readonly string $status')
            ->toContain('public readonly array $tags');        // SET type becomes array (non-nullable)
    });

    it('provides detailed ModelSchema statistics', function () {
        $stats = $this->adapter->getStatistics();

        expect($stats)->toHaveKey('total_field_types');
        expect($stats)->toHaveKey('geometric_types');

        // Verify ModelSchema has many more types than basic Laravel
        expect($stats['total_field_types'])->toBeGreaterThan(60);

        // Verify geometric types are available
        expect($stats['geometric_types'])->toContain('point');
        expect($stats['geometric_types'])->toContain('geometry');
        expect($stats['geometric_types'])->toContain('polygon');
    });

    it('demonstrates the power of ModelSchema field type registry', function () {
        // Show that ModelSchema supports 65+ field types vs basic Laravel's ~20
        $allTypes = $this->adapter->getAvailableFieldTypes();

        // Basic types
        $basicTypes = ['string', 'integer', 'boolean', 'decimal', 'text'];
        foreach ($basicTypes as $type) {
            expect($allTypes)->toContain($type);
        }

        // Advanced types unique to ModelSchema
        $advancedTypes = [
            'uuid', 'email', 'json', 'set', 'enum',
            'point', 'geometry', 'polygon',
            'bigInteger', 'tinyInteger', 'mediumInteger',
            'longText', 'mediumText', 'timestamp',
        ];
        foreach ($advancedTypes as $type) {
            expect($allTypes)->toContain($type);
        }

        // Aliases support
        expect($this->adapter->isFieldTypeSupported('varchar'))->toBeTrue(); // alias for string
        expect($this->adapter->isFieldTypeSupported('int'))->toBeTrue();     // alias for integer
        expect($this->adapter->isFieldTypeSupported('bool'))->toBeTrue();    // alias for boolean
        expect($this->adapter->isFieldTypeSupported('coordinates'))->toBeTrue(); // alias for point
        expect($this->adapter->isFieldTypeSupported('geopoint'))->toBeTrue();    // alias for point

        // This demonstrates ModelSchema's power: 65+ types + aliases vs ~20 basic types
        expect(count($allTypes))->toBeGreaterThan(60);
    });
});
