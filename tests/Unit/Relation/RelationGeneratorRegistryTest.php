<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\RelationGeneratorRegistry;
use Grazulex\LaravelArc\Generator\Relations\HasOneRelationGenerator;

describe('RelationGeneratorRegistry', function () {
    it('delegates to the correct relation generator', function () {
        $registry = new RelationGeneratorRegistry([
            new HasOneRelationGenerator(),
        ]);

        $definition = ['type' => 'hasOne', 'dto' => 'App\DTO\UserDTO'];
        $result = $registry->generate('profile', $definition);

        expect($result)->toBeString()->toContain('$profile');
    });
});
