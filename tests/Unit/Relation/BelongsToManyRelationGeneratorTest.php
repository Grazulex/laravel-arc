<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Relations\BelongsToManyRelationGenerator;
use Grazulex\LaravelArc\Support\DtoNamespaceResolver;

describe('BelongsToManyRelationGenerator', function () {
    it('generates a belongsToMany relation with simple dto name', function () {
        config()->set('dto.dto_namespace', 'App\\DTO');
        $generator = new BelongsToManyRelationGenerator();

        $definition = ['type' => 'belongsToMany', 'dto' => 'TagDTO'];
        $result = $generator->generate('tags', $definition);

        $expected = DtoNamespaceResolver::resolveDtoClass('TagDTO');
        expect($result)->toContain("@var {$expected}[]")->toContain('public array $tags');
    });

    it('generates a belongsToMany relation with full dto class', function () {
        $generator = new BelongsToManyRelationGenerator();

        $definition = ['type' => 'belongsToMany', 'dto' => 'MyApp\\Custom\\RoleDTO'];
        $result = $generator->generate('roles', $definition);

        expect($result)->toContain('@var MyApp\\Custom\\RoleDTO[]')->toContain('public array $roles');
    });

    it('generates a belongsToMany relation with missing dto', function () {
        config()->set('dto.dto_namespace', 'App\\DTO');
        $generator = new BelongsToManyRelationGenerator();

        $definition = ['type' => 'belongsToMany']; // no dto
        $result = $generator->generate('unknowns', $definition);

        expect($result)->toContain('@var App\\DTO\\UNKNOWN[]')->toContain('public array $unknowns');
    });

    it('supports only belongsToMany type', function () {
        $generator = new BelongsToManyRelationGenerator();

        expect($generator->supports('belongsToMany'))->toBeTrue();
        expect($generator->supports('hasMany'))->toBeFalse();
        expect($generator->supports(''))->toBeFalse();
    });
});
