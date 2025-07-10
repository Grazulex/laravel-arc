<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Relations\BelongsToManyRelationGenerator;
use Grazulex\LaravelArc\Support\DtoNamespaceResolver;

describe('BelongsToManyRelationGenerator', function () {
    it('generates a belongsToMany relation with simple dto name', function () {
        config()->set('dto.dto_namespace', 'App\\DTO');
        $generator = new BelongsToManyRelationGenerator();
        $context = new DtoGenerationContext();

        $definition = ['type' => 'belongsToMany', 'dto' => 'RoleDTO'];
        $result = $generator->generate('roles', $definition, $context);

        $expected = DtoNamespaceResolver::resolveDtoClass('RoleDTO');

        expect($result)->toContain("@var {$expected}[]")
            ->toContain('public array $roles');
    });

    it('generates a belongsToMany relation with full dto class', function () {
        $generator = new BelongsToManyRelationGenerator();
        $context = new DtoGenerationContext();

        $definition = ['type' => 'belongsToMany', 'dto' => 'App\\DTO\\PermissionDTO'];
        $result = $generator->generate('permissions', $definition, $context);

        expect($result)->toContain('@var App\\DTO\\PermissionDTO[]')
            ->toContain('public array $permissions');
    });
});
