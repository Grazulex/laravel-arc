<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\FloatValidatorGenerator;

describe('FloatValidatorGenerator', function () {
    it('supports float type', function () {
        $generator = new FloatValidatorGenerator();

        expect($generator->supports('float'))->toBeTrue();
        expect($generator->supports('integer'))->toBeFalse();
    });

    it('generates float (numeric) rule with required and extras', function () {
        $generator = new FloatValidatorGenerator();

        $rules = $generator->generate('price', [
            'type' => 'float',
            'rules' => ['min:0', 'max:999.99'],
        ]);

        expect($rules)->toBe([
            'price' => ['required', 'numeric', 'min:0', 'max:999.99'],
        ]);
    });

    it('generates rule without required if nullable is true', function () {
        $generator = new FloatValidatorGenerator();

        $rules = $generator->generate('price', [
            'type' => 'float',
            'nullable' => true,
        ]);

        expect($rules)->toBe([
            'price' => ['numeric'],
        ]);
    });

    it('returns empty array if type does not match', function () {
        $generator = new FloatValidatorGenerator();

        $rules = $generator->generate('price', ['type' => 'string']);

        expect($rules)->toBe([]);
    });
});
