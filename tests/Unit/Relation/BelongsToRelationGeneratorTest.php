<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Relations\BelongsToRelationGenerator;
use Grazulex\LaravelArc\Support\DtoNamespaceResolver;

describe('BelongsToRelationGenerator', function () {
    it('generates a belongsTo relation with simple dto name', function () {
        config()->set('dto.dto_namespace', 'App\\DTO');
        $generator = new BelongsToRelationGenerator();
        $context = new DtoGenerationContext();

        $definition = ['type' => 'belongsTo', 'dto' => 'UserDTO'];
        $result = $generator->generate('author', $definition, $context);

        $expected = DtoNamespaceResolver::resolveDtoClass('UserDTO');
        expect($result)->toContain("public {$expected} \$author");
    });

    it('generates a belongsTo relation with full dto class', function () {
        $generator = new BelongsToRelationGenerator();
        $context = new DtoGenerationContext();

        $definition = ['type' => 'belongsTo', 'dto' => 'MyApp\\Custom\\AuthorDTO'];
        $result = $generator->generate('author', $definition, $context);

        expect($result)->toContain('public MyApp\\Custom\\AuthorDTO $author');
    });

    it('generates a belongsTo relation with default class when dto is missing', function () {
        config()->set('dto.dto_namespace', 'App\\DTO');
        $generator = new BelongsToRelationGenerator();
        $context = new DtoGenerationContext();

        $definition = ['type' => 'belongsTo'];
        $result = $generator->generate('owner', $definition, $context);

        expect($result)->toContain('public App\\DTO\\UNKNOWN $owner');
    });

    it('generates a belongsTo relation with null dto', function () {
        config()->set('dto.dto_namespace', 'App\\DTO');
        $generator = new BelongsToRelationGenerator();
        $context = new DtoGenerationContext();

        $definition = ['type' => 'belongsTo', 'dto' => null];
        $result = $generator->generate('thing', $definition, $context);

        expect($result)->toContain('public App\\DTO\\UNKNOWN $thing');
    });

    it('generates a belongsTo relation with empty dto string', function () {
        config()->set('dto.dto_namespace', 'App\\DTO');
        $generator = new BelongsToRelationGenerator();
        $context = new DtoGenerationContext();

        $definition = ['type' => 'belongsTo', 'dto' => ''];
        $result = $generator->generate('empty', $definition, $context);

        expect($result)->toContain('public App\\DTO\\UNKNOWN $empty');
    });

    it('supports only the belongsTo type', function () {
        $generator = new BelongsToRelationGenerator();

        expect($generator->supports('belongsTo'))->toBeTrue();
        expect($generator->supports('hasOne'))->toBeFalse();
        expect($generator->supports('hasMany'))->toBeFalse();
        expect($generator->supports(''))->toBeFalse();
    });
});
