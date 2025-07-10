<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Fields\FloatFieldGenerator;

describe('FloatFieldGenerator', function () {
    it('generates float fields correctly', function () {
        $generator = new FloatFieldGenerator();
        $context = new DtoGenerationContext();

        expect($generator->supports('float'))->toBeTrue();
        expect($generator->supports('double'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();

        $code = $generator->generate('name', [
            'type' => 'double',
            'default' => '1.75',
        ], $context);
        expect($code)->toBe('public float $name = 1.75;');

        $code = $generator->generate('name', [
            'type' => 'float',
            'default' => '1.75',
        ], $context);
        expect($code)->toBe('public float $name = 1.75;');

        $code = $generator->generate('name', [
            'type' => 'float',
        ], $context);
        expect($code)->toBe('public float $name;');
    });
});
