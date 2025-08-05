<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Services\ModelSchemaIntegrationService;

describe('ModelSchemaIntegrationService Simple Test', function () {
    it('can be instantiated without recursion', function () {
        $service = new ModelSchemaIntegrationService();
        expect($service)->toBeInstanceOf(ModelSchemaIntegrationService::class);
    });

    it('can process a simple YAML structure', function () {
        // Create a temporary YAML file for testing
        $yamlContent = <<<'YAML'
header:
  dto: TestDTO
  model: App\Models\Test

fields:
  name:
    type: string
    required: true
  
YAML;

        $tempFile = tempnam(sys_get_temp_dir(), 'test_yaml');
        file_put_contents($tempFile, $yamlContent);

        try {
            $service = new ModelSchemaIntegrationService();
            $result = $service->processYamlFile($tempFile);

            expect($result)->toBeArray();
            expect($result)->toHaveKey('header');
            expect($result)->toHaveKey('processed_fields');

        } finally {
            unlink($tempFile);
        }
    });
});
