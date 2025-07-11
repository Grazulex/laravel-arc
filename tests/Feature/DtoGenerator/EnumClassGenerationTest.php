<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerator;
use Symfony\Component\Yaml\Yaml;

describe('Enum Class Generation', function () {
    it('can generate DTO with PHP enum classes', function () {
        $yaml = Yaml::parseFile(__DIR__.'/fixtures/enum-class-dto.yaml');

        $generator = DtoGenerator::make();
        $code = $generator->generateFromDefinition($yaml);

        // Vérifier que le DTO principal est généré
        expect($code)->toContain('final class UserDTO');
        expect($code)->toContain('namespace App\\DTO');

        // Vérifier que l'enum traditionnel est généré comme string
        expect($code)->toContain('public readonly string $status = \'active\',');

        // Vérifier que l'enum PHP est généré avec le bon type
        expect($code)->toContain('public readonly ?\\Tests\\Fixtures\\Enums\\Priority $priority = null,');

        // Vérifier que l'enum PHP avec valeur par défaut est généré
        expect($code)->toContain('public readonly \\Tests\\Fixtures\\Enums\\Status $role = \\Tests\\Fixtures\\Enums\\Status::DRAFT,');

        // Vérifier que les règles de validation sont générées correctement
        expect($code)->toContain("'status' => ['in:active,inactive,suspended', 'required']");
        expect($code)->toContain("'priority' => ['enum:\\Tests\\Fixtures\\Enums\\Priority']");
        expect($code)->toContain("'role' => ['enum:\\Tests\\Fixtures\\Enums\\Status', 'required']");
    });
});
