<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Validators\FloatValidatorGenerator;

describe('FloatValidatorGenerator', function () {
    it('supports float type', function () {
        $generator = new FloatValidatorGenerator();

        expect($generator->supports('float'))->toBeTrue();
        expect($generator->supports('integer'))->toBeFalse();
    });

    it('generates float rule with required and custom rules', function () {
        $generator = new FloatValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('price', [
            'type' => 'float',
            'required' => true,
            'rules' => ['min:0', 'max:999.99'],
        ], $context);

        expect($rules)->toBe([
            'price' => ['numeric', 'required', 'min:0', 'max:999.99'],
        ]);
    });

    it('generates float rule without required if not specified', function () {
        $generator = new FloatValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('price', [
            'type' => 'float',
        ], $context);

        expect($rules)->toBe([
            'price' => ['numeric', 'required'],
        ]);
    });

    it('returns empty array if type does not match', function () {
        $generator = new FloatValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('price', ['type' => 'string'], $context);

        expect($rules)->toBe([]);
    });
});
