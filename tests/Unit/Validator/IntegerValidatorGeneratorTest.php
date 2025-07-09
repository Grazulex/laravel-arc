<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\IntegerValidatorGenerator;

describe('IntegerValidatorGenerator', function () {
    it('supports integer type', function () {
        $generator = new IntegerValidatorGenerator();

        expect($generator->supports('integer'))->toBeTrue();
        expect($generator->supports('float'))->toBeFalse();
    });

    it('generates integer rule with required and extras', function () {
        $generator = new IntegerValidatorGenerator();

        $rules = $generator->generate('quantity', [
            'type' => 'integer',
            'rules' => ['min:1', 'max:100'],
        ]);

        expect($rules)->toBe([
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);
    });

    it('generates rule without required if nullable is true', function () {
        $generator = new IntegerValidatorGenerator();

        $rules = $generator->generate('quantity', [
            'type' => 'integer',
            'nullable' => true,
        ]);

        expect($rules)->toBe([
            'quantity' => ['integer'],
        ]);
    });

    it('returns empty array if type does not match', function () {
        $generator = new IntegerValidatorGenerator();

        $rules = $generator->generate('quantity', ['type' => 'string']);

        expect($rules)->toBe([]);
    });
});
