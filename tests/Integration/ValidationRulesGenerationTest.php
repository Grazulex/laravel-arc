<?php

declare(strict_types=1);

namespace Tests\Integration;

use Grazulex\LaravelArc\Generator\DtoGenerator;
use Tests\TestCase;

final class ValidationRulesGenerationTest extends TestCase
{
    /** @test */
    public function it_generates_validation_rules_from_yaml_validation_field()
    {
        $yamlDefinition = [
            'name' => 'TestUser',
            'table' => 'test_users',
            'relationships' => [],
            'traits' => [],
            'fields' => [
                'email' => [
                    'type' => 'string',
                    'required' => true,
                    'validation' => ['email', 'unique:users'],
                ],
                'age' => [
                    'type' => 'integer',
                    'required' => false,
                    'validation' => ['min:18', 'max:120'],
                ],
                'status' => [
                    'type' => 'string',
                    'required' => false,
                    'validation' => ['in:active,inactive,pending'],
                ],
            ],
        ];

        $generator = DtoGenerator::make();
        $generatedCode = $generator->generateFromDefinition($yamlDefinition);

        // Vérifier que les règles de validation personnalisées sont présentes
        $this->assertStringContainsString("'email' => ['string', 'required', 'email', 'unique:users']", $generatedCode);
        $this->assertStringContainsString("'age' => ['integer', 'nullable', 'min:18', 'max:120']", $generatedCode);
        $this->assertStringContainsString("'status' => ['string', 'nullable', 'in:active,inactive,pending']", $generatedCode);
    }

    /** @test */
    public function it_supports_legacy_rules_field_format()
    {
        $yamlDefinition = [
            'name' => 'TestUser',
            'table' => 'test_users',
            'relationships' => [],
            'traits' => [],
            'fields' => [
                'email' => [
                    'type' => 'string',
                    'required' => true,
                    'rules' => ['email', 'max:255'],
                ],
            ],
        ];

        $generator = DtoGenerator::make();
        $generatedCode = $generator->generateFromDefinition($yamlDefinition);

        // Vérifier que les règles du champ 'rules' sont aussi prises en compte
        $this->assertStringContainsString("'email' => ['string', 'required', 'email', 'max:255']", $generatedCode);
    }

    /** @test */
    public function it_merges_validation_and_rules_fields()
    {
        $yamlDefinition = [
            'name' => 'TestUser',
            'table' => 'test_users',
            'relationships' => [],
            'traits' => [],
            'fields' => [
                'email' => [
                    'type' => 'string',
                    'required' => true,
                    'validation' => ['email'],
                    'rules' => ['unique:users'],
                ],
            ],
        ];

        $generator = DtoGenerator::make();
        $generatedCode = $generator->generateFromDefinition($yamlDefinition);

        // Vérifier que les deux champs sont fusionnés
        $this->assertStringContainsString("'email' => ['string', 'required', 'email', 'unique:users']", $generatedCode);
    }
}
