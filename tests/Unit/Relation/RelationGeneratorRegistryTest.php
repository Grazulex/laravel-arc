<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\RelationGeneratorRegistry;
use Grazulex\LaravelArc\Generator\Relations\HasManyRelationGenerator;
use Grazulex\LaravelArc\Generator\Relations\HasOneRelationGenerator;

describe('RelationGeneratorRegistry', function () {
    it('delegates to the correct hasOne relation generator', function () {
        config()->set('dto.dto_namespace', 'App\\DTO');
        $registry = new RelationGeneratorRegistry([
            new HasOneRelationGenerator(),
            new HasManyRelationGenerator(),
        ]);

        $definition = ['type' => 'hasOne', 'dto' => 'ProfileDTO'];
        $result = $registry->generate('profile', $definition);

        expect($result)->toContain('public App\\DTO\\ProfileDTO $profile');
    });

    it('delegates to the correct hasMany relation generator', function () {
        config()->set('dto.dto_namespace', 'App\\DTO');
        $registry = new RelationGeneratorRegistry([
            new HasOneRelationGenerator(),
            new HasManyRelationGenerator(),
        ]);

        $definition = ['type' => 'hasMany', 'dto' => 'TagDTO'];
        $result = $registry->generate('tags', $definition);

        expect($result)->toContain('@var App\\DTO\\TagDTO[]')
            ->toContain('public array $tags');
    });

    it('returns null if no generator supports the relation type', function () {
        $registry = new RelationGeneratorRegistry([
            new HasOneRelationGenerator(),
        ]);

        $definition = ['type' => 'unknownType', 'dto' => 'X'];
        $result = $registry->generate('x', $definition);

        expect($result)->toBeNull();
    });
    it('returns null when no relation type is provided', function () {
        $registry = new RelationGeneratorRegistry([
            new HasOneRelationGenerator(),
        ]);

        $definition = []; // no 'type' key
        $result = $registry->generate('anything', $definition);

        expect($result)->toBeNull();
    });
    it('uses the first matching generator if multiple support the same type', function () {
        $called = false;

        $fakeGenerator = new class implements Grazulex\LaravelArc\Contracts\RelationGenerator
        {
            public function supports(string $type): bool
            {
                return $type === 'hasOne';
            }

            public function generate(string $name, array $definition): string
            {
                return 'called from fake';
            }
        };

        $registry = new RelationGeneratorRegistry([
            $fakeGenerator,
            new HasOneRelationGenerator(),
        ]);

        $definition = ['type' => 'hasOne', 'dto' => 'UserDTO'];
        $result = $registry->generate('something', $definition);

        expect($result)->toBe('called from fake');
    });
    it('throws an exception if a generator does not implement the RelationGenerator interface', function () {
        $invalidGenerator = new class() {}; // an anonymous class that does NOT implement the interface

        new RelationGeneratorRegistry([$invalidGenerator]);
    })->throws(InvalidArgumentException::class, 'Each generator must implement RelationGenerator.');

});
