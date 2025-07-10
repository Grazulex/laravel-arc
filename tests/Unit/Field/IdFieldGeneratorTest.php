<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Fields\IdFieldGenerator;

describe('IdFieldGenerator', function () {
    it('supports id type', function () {
        $generator = new IdFieldGenerator();

        expect($generator->supports('id'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates nullable id field with null default', function () {
        $generator = new IdFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('user_id', [
            'nullable' => true,
        ], $context);

        expect($code)->toBe('public ?string $user_id = null;');
    });

    it('generates id field with default value', function () {
        $generator = new IdFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('user_id', [
            'default' => 42,
        ], $context);

        expect($code)->toContain('public');
        expect($code)->toContain('$user_id =');
    });
});
