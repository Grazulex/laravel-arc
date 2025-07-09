<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\FloatValidatorGenerator;

describe('FloatValidatorGenerator', function () {
    it('supports float type', function () {
        $generator = new FloatValidatorGenerator();

        expect($generator->supports('float'))->toBeTrue();
        expect($generator->supports('integer'))->toBeFalse();
    });

    it('generates numeric rule with required and custom rules', function () {
        $generator = new FloatValidatorGenerator();

        $rules = $generator->generate('price', [
            'type' => 'float',
            'required' => true,
            'rules' => ['min:0'],
        ]);

        expect($rules)->toBe([
            'price' => ['numeric', 'required', 'min:0'],
        ]);
    });

    it('returns only numeric rule when no extras given', function () {
        $generator = new FloatValidatorGenerator();

        $rules = $generator->generate('price', ['type' => 'float']);

        expect($rules)->toBe([
            'price' => ['numeric'],
        ]);
    });
});
