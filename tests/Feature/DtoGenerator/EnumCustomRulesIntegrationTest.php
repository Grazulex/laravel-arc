<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerator;
use Symfony\Component\Yaml\Yaml;

describe('Enum Custom Rules Integration', function () {
    it('generates dto with custom enum rules', function () {
        $yaml = Yaml::parseFile(__DIR__.'/fixtures/enum-custom-rules.yaml');

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($yaml);

        // Vérifier que le DTO est généré avec les bons types
        expect($result)->toContain('class EnumCustomRulesDto');
        expect($result)->toContain('public readonly \\Tests\\Fixtures\\Enums\\Status $status,');
        expect($result)->toContain('public readonly ?\\Tests\\Fixtures\\Enums\\Priority $priority,');
        expect($result)->toContain('public readonly \\Tests\\Fixtures\\Enums\\Status $category,');
        expect($result)->toContain('public readonly string $legacy_status,');
    });

    it('generates validation rules with custom enum rules', function () {
        $yaml = Yaml::parseFile(__DIR__.'/fixtures/enum-validation-rules.yaml');

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($yaml);

        // Vérifier que les règles de validation sont générées correctement
        expect($result)->toContain("'status' => [");
        expect($result)->toContain("'required']");
        expect($result)->toContain("'enum:".'\\Tests\\Fixtures\\Enums\\Status'."'");
        expect($result)->toContain("'in_enum:".'\\Tests\\Fixtures\\Enums\\Status'."'");

        expect($result)->toContain("'priority' => [");
        expect($result)->toContain("'enum:".'\\Tests\\Fixtures\\Enums\\Priority'."'");
        expect($result)->toContain("'enum_exists:".'\\Tests\\Fixtures\\Enums\\Priority'."'");

        expect($result)->toContain("'category' => [");
        expect($result)->toContain("'enum_exists:".'\\Tests\\Fixtures\\Enums\\Status'."'");
        expect($result)->toContain("'in_enum:".'\\Tests\\Fixtures\\Enums\\Status'."'");

        expect($result)->toContain("'legacy_status' => [");
        expect($result)->toContain("'in:pending,active,completed'");
        // Les règles personnalisées doivent être ignorées pour les enums traditionnels
        expect($result)->not->toContain("'in_enum:pending");
        expect($result)->not->toContain("'enum_exists:pending");
    });

    it('works with default values', function () {
        $yaml = Yaml::parseFile(__DIR__.'/fixtures/enum-default-values.yaml');

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($yaml);

        // Vérifier que les valeurs par défaut sont correctement générées
        expect($result)->toContain('public readonly \Tests\Fixtures\Enums\Status $status = \Tests\Fixtures\Enums\Status::DRAFT,');
        expect($result)->toContain('public readonly \Tests\Fixtures\Enums\Priority $priority = \Tests\Fixtures\Enums\Priority::LOW,');

        // Vérifier que les règles personnalisées sont présentes
        expect($result)->toContain("'in_enum:".'\\Tests\\Fixtures\\Enums\\Status'."'");
        expect($result)->toContain("'enum_exists:".'\\Tests\\Fixtures\\Enums\\Priority'."'");
    });

    it('handles complex enum configurations', function () {
        $yaml = Yaml::parseFile(__DIR__.'/fixtures/complex-enum.yaml');

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($yaml);

        // Vérifier les types générés
        expect($result)->toContain('class ComplexEnumDto');
        expect($result)->toContain('public readonly \\Tests\\Fixtures\\Enums\\Status $status = \\Tests\\Fixtures\\Enums\\Status::DRAFT,');
        expect($result)->toContain('public readonly ?\\Tests\\Fixtures\\Enums\\Priority $priority,');
        expect($result)->toContain('public readonly string $category,');
        expect($result)->toContain('public readonly ?\\Tests\\Fixtures\\Enums\\Status $type = \\Tests\\Fixtures\\Enums\\Status::PUBLISHED,');

        // Vérifier les règles de validation
        expect($result)->toContain("'status' => ['enum:\Tests\Fixtures\Enums\Status', 'in_enum:\Tests\Fixtures\Enums\Status', 'enum_exists:\Tests\Fixtures\Enums\Status', 'required', 'sometimes']");
        expect($result)->toContain("'priority' => ['enum:\Tests\Fixtures\Enums\Priority', 'enum_exists:\Tests\Fixtures\Enums\Priority', 'required']");
        expect($result)->toContain("'category' => ['in:tech,business,personal', 'required', 'sometimes']");
        expect($result)->toContain("'type' => ['enum:\Tests\Fixtures\Enums\Status', 'in_enum:\Tests\Fixtures\Enums\Status', 'nullable']");

        // Vérifier que les règles personnalisées sont ignorées pour les enums traditionnels
        expect($result)->not->toContain("'in_enum:tech'");
        expect($result)->not->toContain("'enum_exists:tech'");
    });
});
