<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Fields\ArrayFieldGenerator;

describe('ArrayFieldGenerator', function () {
    it('supports array type', function () {
        $generator = new ArrayFieldGenerator();

        expect($generator->supports('array'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates non-required array field with null default', function () {
        $generator = new ArrayFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('items', [
            'required' => false,
        ], $context);

        expect($code)->toBe('public ?array $items = null;');
    });

    it('generates array field with default value', function () {
        $generator = new ArrayFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('items', [
            'default' => ['a', 'b', 'c'],
        ], $context);

        expect($code)->toContain('public');
        expect($code)->toContain('$items =');
    });
});
