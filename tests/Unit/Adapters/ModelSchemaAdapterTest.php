<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Adapters\ModelSchemaAdapter;

describe('ModelSchemaAdapter', function () {
    beforeEach(function () {
        $this->adapter = new ModelSchemaAdapter();
    });

    it('can be instantiated', function () {
        expect($this->adapter)->toBeInstanceOf(ModelSchemaAdapter::class);
    });

    it('provides access to ModelSchema field types', function () {
        $fieldTypes = $this->adapter->getAvailableFieldTypes();
        
        expect($fieldTypes)->toBeArray();
        expect(count($fieldTypes))->toBeGreaterThan(60); // ModelSchema has 65+ types
        
        // Check for basic types
        expect($fieldTypes)->toContain('string');
        expect($fieldTypes)->toContain('integer');
        expect($fieldTypes)->toContain('boolean');
        
        // Check for advanced types
        expect($fieldTypes)->toContain('uuid');
        expect($fieldTypes)->toContain('geometry');
        expect($fieldTypes)->toContain('point');
    });

    it('can check if field types are supported', function () {
        expect($this->adapter->isFieldTypeSupported('string'))->toBeTrue();
        expect($this->adapter->isFieldTypeSupported('geometry'))->toBeTrue();
        expect($this->adapter->isFieldTypeSupported('unknown_type'))->toBeFalse();
    });

    it('can resolve field type aliases', function () {
        // Test common aliases
        expect($this->adapter->resolveFieldTypeAlias('varchar'))->toBe('string');
        expect($this->adapter->resolveFieldTypeAlias('int'))->toBe('integer');
        expect($this->adapter->resolveFieldTypeAlias('bool'))->toBe('boolean');
        
        // Test geometric aliases
        expect($this->adapter->resolveFieldTypeAlias('coordinates'))->toBe('point');
        expect($this->adapter->resolveFieldTypeAlias('geopoint'))->toBe('point');
    });

    it('provides statistics about ModelSchema capabilities', function () {
        $stats = $this->adapter->getStatistics();
        
        expect($stats)->toHaveKey('total_field_types');
        expect($stats)->toHaveKey('base_field_types');
        expect($stats)->toHaveKey('aliases_count');
        expect($stats)->toHaveKey('sample_types');
        expect($stats)->toHaveKey('geometric_types');
        
        expect($stats['total_field_types'])->toBeGreaterThan(60);
        expect($stats['geometric_types'])->toBeArray();
        expect($stats['sample_types'])->toBeArray();
    });

    it('can enhance YAML data with ModelSchema field type information', function () {
        $yamlData = [
            'header' => [
                'dto' => 'TestDTO',
                'model' => 'App\Models\Test',
            ],
            'fields' => [
                'name' => [
                    'type' => 'string',
                    'required' => true,
                ],
                'coordinates' => [
                    'type' => 'point',
                    'nullable' => true,
                ],
                'unknown_field' => [
                    'type' => 'unknown_type',
                    'nullable' => true,
                ],
            ],
        ];

        // Use reflection to test the protected method
        $reflection = new ReflectionClass($this->adapter);
        $method = $reflection->getMethod('enhanceFieldTypes');
        $method->setAccessible(true);
        
        $enhanced = $method->invoke($this->adapter, $yamlData);
        
        expect($enhanced['fields']['name'])->toHaveKey('_modelschema');
        expect($enhanced['fields']['coordinates'])->toHaveKey('_modelschema');
        
        // Known field types should have ModelSchema metadata
        $nameMetadata = $enhanced['fields']['name']['_modelschema'];
        expect($nameMetadata)->toHaveKey('type_class');
        expect($nameMetadata)->toHaveKey('cast_type');
        expect($nameMetadata)->toHaveKey('aliases');
        
        // Unknown field types should not break the process
        expect($enhanced['fields']['unknown_field'])->not->toHaveKey('_modelschema');
    });
});
