<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Fields\IntegerFieldGenerator;

describe('IntegerFieldGenerator', function () {
    it('generates integer fields correctly', function () {
        $generator = new IntegerFieldGenerator();
        $context = new DtoGenerationContext();

        expect($generator->supports('int'))->toBeTrue();
        expect($generator->supports('integer'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();

        $code = $generator->generate('name', [
            'type' => 'int',
            'default' => 100,
        ], $context);

        expect($code)->toBe('public int $name = 100;');

        $code = $generator->generate('name', [
            'type' => 'integer',
            'default' => 100,
        ], $context);

        expect($code)->toBe('public int $name = 100;');

        $code = $generator->generate('name', [
            'type' => 'integer',
        ], $context);

        expect($code)->toBe('public int $name;');
    });
});
