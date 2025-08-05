<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Services\MinimalModelSchemaIntegrationService;
use Grazulex\LaravelArc\Generator\DtoGenerator;

describe('ModelSchema Direct Integration', function () {
    it('can generate DTO code with advanced field types using direct integration service', function () {
        $yamlFile = __DIR__ . '/DtoGenerator/fixtures/advanced-modelschema.yaml';
        
        // Use the integration service directly to avoid adapter recursion
        $integrationService = new MinimalModelSchemaIntegrationService();
        $processedData = $integrationService->processYamlFile($yamlFile);
        
        // Convert to Arc format
        $yaml = [
            'header' => $processedData['header'],
            'fields' => $processedData['processed_fields'],
            'relations' => $processedData['relations'],
            'options' => $processedData['options'],
        ];

        $code = DtoGenerator::make()->generateFromDefinition($yaml);

        expect($code)
            ->toContain('final class AdvancedLocationDTO')
            ->toContain('public readonly string $id')
            ->toContain('public readonly string $name')
            ->toContain('public readonly string $coordinates')  // Point type becomes string
            ->toContain('public readonly string $boundary')     // Polygon type becomes string
            ->toContain('public readonly string $email')
            ->toContain('public readonly string $website')
            ->toContain('public readonly array $metadata')     // JSON type becomes array
            ->toContain('public readonly string $price')        // Fixed: should be string, not float
            ->toContain('public readonly string $status')
            ->toContain('public readonly array $tags');        // SET type becomes array
    });

    it('can provide integration statistics without recursion', function () {
        $integrationService = new MinimalModelSchemaIntegrationService();
        $stats = $integrationService->getIntegrationStatistics();

        expect($stats)->toHaveKey('total_modelschema_types');
        expect($stats)->toHaveKey('geometric_types_count');

        // Verify ModelSchema integration is working with basic type mapping
        expect($stats['total_modelschema_types'])->toBeGreaterThan(5);
        
        // Verify geometric types are available
        expect($stats['geometric_types_count'])->toBeGreaterThan(0);
    });

    it('demonstrates ModelSchema field processing power', function () {
        $yamlFile = __DIR__ . '/DtoGenerator/fixtures/advanced-modelschema.yaml';
        
        $integrationService = new MinimalModelSchemaIntegrationService();
        $processedData = $integrationService->processYamlFile($yamlFile);
        
        // Verify all advanced fields are processed
        $fields = $processedData['processed_fields'];
        
        expect($fields)->toHaveKey('coordinates');
        expect($fields)->toHaveKey('boundary');
        expect($fields)->toHaveKey('email');
        expect($fields)->toHaveKey('metadata');
        expect($fields)->toHaveKey('tags');
        
        // Verify type mapping worked
        expect($fields['coordinates']['type'])->toBe('string'); // point → string
        expect($fields['boundary']['type'])->toBe('string');    // polygon → string
        expect($fields['metadata']['type'])->toBe('array');     // json → array
        expect($fields['tags']['type'])->toBe('array');         // set → array
        
        // Verify ModelSchema metadata is preserved
        expect($fields['coordinates'])->toHaveKey('_modelschema');
        expect($fields['boundary'])->toHaveKey('_modelschema');
        expect($fields['email'])->toHaveKey('_modelschema');
        expect($fields['metadata'])->toHaveKey('_modelschema');
        expect($fields['tags'])->toHaveKey('_modelschema');
    });
});
