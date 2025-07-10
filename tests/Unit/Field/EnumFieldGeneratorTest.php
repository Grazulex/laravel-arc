<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Fields\EnumFieldGenerator;

describe('EnumFieldGenerator', function () {
    it('supports enum type', function () {
        $generator = new EnumFieldGenerator();

        expect($generator->supports('enum'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates non-required enum field with null default', function () {
        $generator = new EnumFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('status', [
            'required' => false,
        ], $context);

        expect($code)->toBe('public ?string $status = null;');
    });

    it('generates enum field with default value', function () {
        $generator = new EnumFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('status', [
            'default' => 'draft',
        ], $context);

        expect($code)->toContain('public');
        expect($code)->toContain('$status =');
    });
});
