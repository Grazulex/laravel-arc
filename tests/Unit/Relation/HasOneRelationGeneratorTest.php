<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Relations\HasOneRelationGenerator;

describe('HasOneRelationGenerator', function () {
    it('generates a hasOne relation property', function () {
        $generator = new HasOneRelationGenerator();

        $definition = ['type' => 'hasOne', 'dto' => 'App\DTO\UserDTO'];
        $result = $generator->generate('profile', $definition);

        expect($result)->toContain('public App\\DTO\\UserDTO $profile');
    });
});
