<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Relations\BelongsToManyRelationGenerator;

describe('BelongsToManyRelationGenerator Edge Cases', function () {
    beforeEach(function () {
        $this->generator = new BelongsToManyRelationGenerator();
        $this->context = new DtoGenerationContext();
    });

    it('supports belongsToMany relation type', function () {
        expect($this->generator->supports('belongsToMany'))->toBe(true);
        expect($this->generator->supports('belongs_to_many'))->toBe(false);
        expect($this->generator->supports('hasMany'))->toBe(false);
        expect($this->generator->supports('hasOne'))->toBe(false);
    });

    it('generates belongsToMany relation with array return type', function () {
        $config = [
            'type' => 'belongsToMany',
            'dto' => 'TagDto',
        ];

        $result = $this->generator->generate('tags', $config, $this->context);

        expect($result)->toBeString();
        expect($result)->toContain('public array $tags');
        expect($result)->toContain('/** @var App\\DTO\\TagDto[] */');
    });

    it('generates belongsToMany relation with null dto', function () {
        $config = [
            'type' => 'belongsToMany',
            'dto' => null,
        ];

        $result = $this->generator->generate('tags', $config, $this->context);

        expect($result)->toBeString();
        expect($result)->toContain('public array $tags');
        expect($result)->toContain('/** @var App\\DTO\\UNKNOWN[] */');
    });

    it('generates belongsToMany relation with empty dto', function () {
        $config = [
            'type' => 'belongsToMany',
            'dto' => '',
        ];

        $result = $this->generator->generate('tags', $config, $this->context);

        expect($result)->toBeString();
        expect($result)->toContain('public array $tags');
        expect($result)->toContain('/** @var App\\DTO\\UNKNOWN[] */');
    });

    it('generates belongsToMany relation with fully qualified class name', function () {
        $config = [
            'type' => 'belongsToMany',
            'dto' => 'App\\DTOs\\TagDto',
        ];

        $result = $this->generator->generate('tags', $config, $this->context);

        expect($result)->toBeString();
        expect($result)->toContain('public array $tags');
        expect($result)->toContain('/** @var App\\DTOs\\TagDto[] */');
    });

    it('handles complex relation names', function () {
        $config = [
            'type' => 'belongsToMany',
            'dto' => 'UserRoleDto',
        ];

        $result = $this->generator->generate('user_roles', $config, $this->context);

        expect($result)->toBeString();
        expect($result)->toContain('public array $user_roles');
        expect($result)->toContain('/** @var App\\DTO\\UserRoleDto[] */');
    });
});
