<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Relations\HasManyRelationGenerator;
use Grazulex\LaravelArc\Support\DtoNamespaceResolver;

describe('HasManyRelationGenerator', function () {
    it('generates a hasMany relation with simple dto name', function () {
        config()->set('dto.dto_namespace', 'App\\DTO');
        $generator = new HasManyRelationGenerator();

        $definition = ['type' => 'hasMany', 'dto' => 'CommentDTO'];
        $result = $generator->generate('comments', $definition);

        $expected = DtoNamespaceResolver::resolveDtoClass('CommentDTO');
        expect($result)->toContain("@var {$expected}[]")->toContain('public array $comments');
    });

    it('generates a hasMany relation with full dto class', function () {
        $generator = new HasManyRelationGenerator();

        $definition = ['type' => 'hasMany', 'dto' => 'App\\DTO\\OrderDTO'];
        $result = $generator->generate('orders', $definition);

        expect($result)->toContain('@var App\\DTO\\OrderDTO[]')->toContain('public array $orders');
    });
});
