<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Relations\HasOneRelationGenerator;
use Grazulex\LaravelArc\Support\DtoNamespaceResolver;

describe('HasOneRelationGenerator', function () {
    it('generates a hasOne relation with simple dto name', function () {
        config()->set('dto.dto_namespace', 'App\\DTO');
        $generator = new HasOneRelationGenerator();

        $definition = ['type' => 'hasOne', 'dto' => 'UserDTO'];
        $result = $generator->generate('user', $definition, new DtoGenerationContext());

        $expected = DtoNamespaceResolver::resolveDtoClass('UserDTO');
        expect($result)->toContain("public {$expected} \$user");
    });

    it('generates a hasOne relation with full dto class', function () {
        $generator = new HasOneRelationGenerator();

        $definition = ['type' => 'hasOne', 'dto' => 'Custom\\Namespace\\ProfileDTO'];
        $result = $generator->generate('profile', $definition, new DtoGenerationContext());

        expect($result)->toContain('public Custom\\Namespace\\ProfileDTO $profile');
    });
});
