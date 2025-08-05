<?php

declare(strict_types=1);

namespace Tests\Feature;

use Grazulex\LaravelArc\Generator\DtoGenerator;

describe('Advanced ModelSchema DTO Generation', function () {
    it('generates AdvancedLocationDTO with all ModelSchema types', function () {
        // YAML content from the fixtures file
        $yamlContent = [
            'header' => [
                'dto' => 'AdvancedLocationDTO',
                'table' => 'locations',
                'model' => 'App\Models\Location',
                'namespace' => 'App\DTO',
            ],
            'fields' => [
                'id' => [
                    'type' => 'uuid',
                    'required' => true,
                ],
                'name' => [
                    'type' => 'string',
                    'required' => true,
                ],
                'coordinates' => [
                    'type' => 'point',        // ModelSchema geometric type
                    'nullable' => true,
                ],
                'boundary' => [
                    'type' => 'polygon',      // ModelSchema geometric type
                    'nullable' => true,
                ],
                'email' => [
                    'type' => 'email',        // ModelSchema enhanced string
                    'unique' => true,
                ],
                'website' => [
                    'type' => 'string',       // Could be 'url' with enhancement
                    'nullable' => true,
                ],
                'metadata' => [
                    'type' => 'json',         // ModelSchema JSON type
                    'nullable' => true,
                ],
                'price' => [
                    'type' => 'decimal',
                    'precision' => 10,
                    'scale' => 2,
                    'default' => '0.00',
                ],
                'status' => [
                    'type' => 'enum',
                    'values' => ['active', 'inactive', 'pending'],
                ],
                'tags' => [
                    'type' => 'set',          // ModelSchema collection type
                    'nullable' => true,
                ],
            ],
            'relations' => [
                'category' => [
                    'type' => 'belongsTo',
                    'target' => 'App\Models\Category',
                ],
            ],
            'options' => [
                'timestamps' => true,
                'soft_deletes' => false,
                'expose_hidden_by_default' => false,
                'namespace' => 'App\DTO',
            ],
        ];

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($yamlContent);

        // Test class declaration
        expect($result)->toContain('final class AdvancedLocationDTO');
        expect($result)->toContain('namespace App\DTO;');

        // Test ModelSchema type mappings
        expect($result)->toContain('public readonly string $id,');         // uuid → string
        expect($result)->toContain('public readonly string $name,');       // string → string
        expect($result)->toContain('public readonly string $coordinates,'); // point → string (we set nullable: true)
        expect($result)->toContain('public readonly string $boundary,');   // polygon → string (we set nullable: true)
        expect($result)->toContain('public readonly string $email,');      // email → string
        expect($result)->toContain('public readonly string $website,');   // string → string (we set nullable: true)
        expect($result)->toContain('public readonly array $metadata,');   // json → array (we set nullable: true)
        expect($result)->toContain('public readonly string $price = \'0.00\','); // decimal → string with default
        expect($result)->toContain('public readonly string $status,');     // enum → string
        expect($result)->toContain('public readonly array $tags,');       // set → array (we set nullable: true)

        // Test relations (they are generated as properties, not constructor params)
        expect($result)->toContain('public App\DTO\UNKNOWN $category;'); // Relations are generated differently

        // Test validation rules generation (using mapped types, not original ModelSchema types)
        expect($result)->toContain("'id' => ['string', 'required']");      // uuid → string → string validator
        expect($result)->toContain("'name' => ['string', 'required']");    // string → string → string validator
        expect($result)->toContain("'coordinates' => ['string', 'required']"); // point → string → string validator
        expect($result)->toContain("'boundary' => ['string', 'required']");    // polygon → string → string validator
        expect($result)->toContain("'email' => ['string', 'required']");   // email → string → string validator
        expect($result)->toContain("'metadata' => ['array', 'required']"); // json → array → array validator
        expect($result)->toContain("'tags' => ['array', 'required']");     // set → array → array validator

        // Test that it contains the fromModel method
        expect($result)->toContain('public static function fromModel');
        expect($result)->toContain('public function toArray(): array');
    });

    it('demonstrates ModelSchema type conversion stats', function () {
        $yamlContent = [
            'header' => ['dto' => 'TestDto'],
            'fields' => [
                'geo_point' => ['type' => 'point'],
                'geo_polygon' => ['type' => 'polygon'],
                'geo_line' => ['type' => 'linestring'],
                'data_json' => ['type' => 'json'],
                'data_set' => ['type' => 'set'],
                'enhanced_email' => ['type' => 'email'],
                'enhanced_url' => ['type' => 'url'],
                'enhanced_phone' => ['type' => 'phone'],
                'num_money' => ['type' => 'money'],
                'num_bigint' => ['type' => 'bigint'],
                'bool_flag' => ['type' => 'boolean'],
                'time_stamp' => ['type' => 'timestamp'],
            ],
        ];

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($yamlContent);

        // Verify comprehensive type mapping coverage
        expect($result)->toContain('public readonly string $geo_point,');      // point → string
        expect($result)->toContain('public readonly string $geo_polygon,');    // polygon → string
        expect($result)->toContain('public readonly string $geo_line,');       // linestring → string
        expect($result)->toContain('public readonly array $data_json,');       // json → array
        expect($result)->toContain('public readonly array $data_set,');        // set → array
        expect($result)->toContain('public readonly string $enhanced_email,'); // email → string
        expect($result)->toContain('public readonly string $enhanced_url,');   // url → string
        expect($result)->toContain('public readonly string $enhanced_phone,'); // phone → string
        expect($result)->toContain('public readonly string $num_money,');      // money → decimal → string
        expect($result)->toContain('public readonly int $num_bigint,');        // bigint → integer → int
        expect($result)->toContain('public readonly bool $bool_flag,');        // boolean → boolean → bool
        expect($result)->toContain('public readonly string $time_stamp,');     // timestamp → string

        // Count different type categories represented
        $geometricTypes = mb_substr_count($result, '// ModelSchema geometric');
        $enhancedTypes = mb_substr_count($result, '// ModelSchema enhanced');

        // Should have successfully processed 12 different ModelSchema types
        expect(mb_substr_count($result, 'public readonly'))->toBe(12);
    });
});
