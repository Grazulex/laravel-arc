<?php

declare(strict_types=1);

namespace Tests\Unit\Generator;

use Grazulex\LaravelArc\Generator\DtoTemplateRenderer;
use Tests\TestCase;

final class DtoTemplateRendererFromArrayTest extends TestCase
{
    public function test_it_generates_from_array_method_without_transformers(): void
    {
        $fields = [
            'name' => ['type' => 'string'],
            'email' => ['type' => 'string'],
        ];

        $renderer = new DtoTemplateRenderer();
        $result = $renderer->renderFromArray($fields);

        $this->assertStringContainsString('public static function fromArray(array $data): self', $result);
        $this->assertStringContainsString('name: $data[\'name\'] ?? null,', $result);
        $this->assertStringContainsString('email: $data[\'email\'] ?? null,', $result);
        $this->assertStringNotContainsString('FieldTransformerRegistry', $result);
    }

    public function test_it_generates_from_array_method_with_transformers(): void
    {
        $fields = [
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
        ];

        $renderer = new DtoTemplateRenderer();
        $result = $renderer->renderFromArray($fields);

        $this->assertStringContainsString('public static function fromArray(array $data): self', $result);
        $this->assertStringContainsString('FieldTransformerRegistry', $result);
        $this->assertStringContainsString('name: $registry->transform($data[\'name\'] ?? null, [\'trim\', \'title_case\']),', $result);
        $this->assertStringContainsString('email: $registry->transform($data[\'email\'] ?? null, [\'trim\', \'lowercase\']),', $result);
        $this->assertStringContainsString('status: $data[\'status\'] ?? null,', $result);
    }

    public function test_it_handles_empty_transformers_array(): void
    {
        $fields = [
            'name' => [
                'type' => 'string',
                'transformers' => [],
            ],
        ];

        $renderer = new DtoTemplateRenderer();
        $result = $renderer->renderFromArray($fields);

        $this->assertStringContainsString('name: $data[\'name\'] ?? null,', $result);
        $this->assertStringNotContainsString('FieldTransformerRegistry', $result);
    }
}
