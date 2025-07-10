<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Fields\UuidFieldGenerator;

describe('UuidFieldGenerator', function () {
    it('supports uuid type', function () {
        $generator = new UuidFieldGenerator();

        expect($generator->supports('uuid'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates nullable uuid field with null default', function () {
        $generator = new UuidFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('uuid', [
            'required' => false,
        ], $context);

        expect($code)->toBe('public ?string $uuid = null;');
    });

    it('generates uuid field with default value', function () {
        $generator = new UuidFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('uuid', [
            'default' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
        ], $context);

        expect($code)->toContain('public');
        expect($code)->toContain('$uuid =');
    });
});
