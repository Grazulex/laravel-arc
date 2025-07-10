<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Fields\BooleanFieldGenerator;

describe('BooleanFieldGenerator', function () {
    it('generates boolean fields correctly', function () {
        $generator = new BooleanFieldGenerator();
        $context = new DtoGenerationContext();

        expect($generator->supports('bool'))->toBeTrue();
        expect($generator->supports('boolean'))->toBeTrue();
        expect($generator->supports('integer'))->toBeFalse();

        $code = $generator->generate('name', [
            'type' => 'boolean',
            'default' => true,
        ], $context);

        expect($code)->toBe('public bool $name = true;');

        $code = $generator->generate('name', [
            'type' => 'bool',
        ], $context);

        expect($code)->toBe('public bool $name;');
    });
});
