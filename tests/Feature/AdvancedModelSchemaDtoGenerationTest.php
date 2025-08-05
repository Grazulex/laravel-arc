<?php

declare(strict_types=1);

namespace Tests\Feature;

use Grazulex\LaravelArc\Generator\DtoGenerator;
use Grazulex\LaravelArc\Services\AdvancedModelSchemaIntegrationService;

describe('Advanced ModelSchema DTO Generation', function () {
    it('generates AdvancedLocationDTO with all ModelSchema types', function () {
        // Create YAML file with Arc + ModelSchema compatible structure
        $yamlContent = <<<'YAML'
header:
  dto: AdvancedLocationDTO
  table: locations
  model: App\Models\Location

fields:
  id:
    type: uuid
    required: true
  name:
    type: string
    required: true
  coordinates:
    type: point
    nullable: true
  boundary:
    type: polygon
    nullable: true
  metadata:
    type: json
    nullable: true
  tags:
    type: set
    nullable: true
  email:
    type: email
    required: true
  website:
    type: url
    nullable: true
  price:
    type: decimal
    precision: 10
    scale: 2
    default: "0.00"
  status:
    type: enum
    values: [active, inactive, pending]

options:
  timestamps: true
  soft_deletes: false
  expose_hidden_by_default: false
  namespace: App\DTO
YAML;

        // Create temporary YAML file
        $yamlFile = '/tmp/advanced-location.yaml';
        file_put_contents($yamlFile, $yamlContent);

        // Use our AdvancedModelSchemaIntegrationService
        $service = new AdvancedModelSchemaIntegrationService();
        $processedData = $service->processYamlFile($yamlFile);

        // Generate DTO using processed data
        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($processedData, $yamlFile);

        // Test class declaration
        expect($result)->toContain('final class AdvancedLocationDTO');
        expect($result)->toContain('namespace App\DTO;');

        // Test ModelSchema type mappings (all converted to Arc-compatible types)
        expect($result)->toContain('public readonly string $id,');         // uuid → string
        expect($result)->toContain('public readonly string $name,');       // string → string
        expect($result)->toContain('public readonly string $coordinates,'); // point → string (nullable in YAML, but Arc doesn't handle ? yet)
        expect($result)->toContain('public readonly string $boundary,');   // polygon → string (nullable in YAML, but Arc doesn't handle ? yet)
        expect($result)->toContain('public readonly array $metadata,');    // json → array (nullable in YAML, but Arc doesn't handle ? yet)
        expect($result)->toContain('public readonly array $tags,');        // set → array (nullable in YAML, but Arc doesn't handle ? yet)
        expect($result)->toContain('public readonly string $email,');      // email → string
        expect($result)->toContain('public readonly string $website,');   // url → string (nullable in YAML, but Arc doesn't handle ? yet)
        expect($result)->toContain('public readonly string $price,');      // decimal → string (default handled separately)
        expect($result)->toContain('public readonly string $status,');     // enum → string

        // Test validation rules generation (using mapped types, Arc adds extra rules automatically)
        expect($result)->toContain("'id' => ['string', 'required', 'uuid']");  // uuid type adds uuid rule
        expect($result)->toContain("'name' => ['string', 'required']");
        expect($result)->toContain("'coordinates' => ['string', 'required', 'nullable']");
        expect($result)->toContain("'boundary' => ['string', 'required', 'nullable']");
        expect($result)->toContain("'email' => ['string', 'required', 'email']");  // email type adds email rule
        expect($result)->toContain("'metadata' => ['array', 'required', 'nullable']");
        expect($result)->toContain("'tags' => ['array', 'required', 'nullable']");

        // Test that it contains the required methods
        expect($result)->toContain('public static function fromModel');
        expect($result)->toContain('public function toArray(): array');

        // Clean up
        unlink($yamlFile);
    });

    it('demonstrates ModelSchema type conversion stats', function () {
        // Create YAML with comprehensive ModelSchema types
        $yamlContent = <<<'YAML'
header:
  dto: TestDto

fields:
  geo_point:
    type: point
  geo_polygon:
    type: polygon
  geo_line:
    type: linestring
  data_json:
    type: json
  data_set:
    type: set
  enhanced_email:
    type: email
  enhanced_url:
    type: url
  enhanced_phone:
    type: phone
  num_money:
    type: money
  num_bigint:
    type: bigint
  bool_flag:
    type: boolean
  time_stamp:
    type: timestamp

options:
  namespace: App\DTO
YAML;

        // Create temporary YAML file
        $yamlFile = '/tmp/test-dto.yaml';
        file_put_contents($yamlFile, $yamlContent);

        // Use our AdvancedModelSchemaIntegrationService
        $service = new AdvancedModelSchemaIntegrationService();
        $processedData = $service->processYamlFile($yamlFile);

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($processedData, $yamlFile);

        // Verify comprehensive type mapping coverage (all types mapped to Arc-compatible types)
        expect($result)->toContain('public readonly string $geo_point,');      // point → string
        expect($result)->toContain('public readonly string $geo_polygon,');    // polygon → string
        expect($result)->toContain('public readonly string $geo_line,');       // linestring → string
        expect($result)->toContain('public readonly array $data_json,');       // json → array (via getCastType)
        expect($result)->toContain('public readonly array $data_set,');        // set → array (via getCastType)
        expect($result)->toContain('public readonly string $enhanced_email,'); // email → string
        expect($result)->toContain('public readonly string $enhanced_url,');   // url → string
        expect($result)->toContain('public readonly string $enhanced_phone,'); // phone → string
        expect($result)->toContain('public readonly string $num_money,');      // money → string
        expect($result)->toContain('public readonly string $num_bigint,');     // bigint → string (ModelSchema doesn't cast to integer)
        expect($result)->toContain('public readonly bool $bool_flag,');        // boolean → bool
        expect($result)->toContain('public readonly string $time_stamp,');     // timestamp → string

        // Should have successfully processed 12 different ModelSchema types
        expect(mb_substr_count($result, 'public readonly'))->toBe(12);

        // Clean up
        unlink($yamlFile);
    });
});
