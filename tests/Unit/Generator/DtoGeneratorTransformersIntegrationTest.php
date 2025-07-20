<?php

declare(strict_types=1);

namespace Tests\Unit\Generator;

use Grazulex\LaravelArc\Generator\DtoGenerator;
use Tests\TestCase;

final class DtoGeneratorTransformersIntegrationTest extends TestCase
{
    public function test_it_generates_dto_with_transformers_and_correct_model_fqcn(): void
    {
        $yaml = [
            'header' => [
                'dto' => 'UserDTO',
                'namespace' => 'App\\DTO',
                'model' => 'App\\Models\\User',
            ],
            'fields' => [
                'name' => [
                    'type' => 'string',
                    'transformers' => ['trim', 'title_case'],
                ],
                'email' => [
                    'type' => 'string',
                    'transformers' => ['trim', 'lowercase'],
                ],
                'status' => [
                    'type' => 'string',
                ],
            ],
        ];

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($yaml);

        // Test that the model FQCN is correct (Bug 1 & 2 fixed)
        $this->assertStringContainsString('fromModel(\\App\\Models\\User $model)', $result);
        $this->assertStringNotContainsString('fromModel(\\App\\Models\\Model $model)', $result);

        // Test that fromArray method is generated with transformers (Feature request)
        $this->assertStringContainsString('public static function fromArray(array $data): self', $result);
        $this->assertStringContainsString('FieldTransformerRegistry', $result);
        $this->assertStringContainsString('name: $registry->transform($data[\'name\'] ?? null, [\'trim\', \'title_case\']),', $result);
        $this->assertStringContainsString('email: $registry->transform($data[\'email\'] ?? null, [\'trim\', \'lowercase\']),', $result);
        $this->assertStringContainsString('status: $data[\'status\'] ?? null,', $result);

        // Test basic DTO structure
        $this->assertStringContainsString('namespace App\\DTO;', $result);
        $this->assertStringContainsString('final class UserDTO', $result);
        $this->assertStringContainsString('public readonly string $name,', $result);
        $this->assertStringContainsString('public readonly string $email,', $result);
        $this->assertStringContainsString('public readonly string $status,', $result);
    }

    public function test_it_handles_dto_without_model_definition(): void
    {
        $yaml = [
            'header' => [
                'dto' => 'SimpleDTO',
                'namespace' => 'App\\DTO',
            ],
            'fields' => [
                'name' => [
                    'type' => 'string',
                    'transformers' => ['trim'],
                ],
            ],
        ];

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($yaml);

        // Should fall back to default model
        $this->assertStringContainsString('fromModel(\\App\\Models\\Model $model)', $result);
        $this->assertStringContainsString('public static function fromArray(array $data): self', $result);
        $this->assertStringContainsString('FieldTransformerRegistry', $result);
    }
}
