<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\BooleanValidatorGenerator;

describe('BooleanValidatorGenerator', function () {
    it('supports boolean type', function () {
        $generator = new BooleanValidatorGenerator();

        expect($generator->supports('boolean'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates boolean rule with required and custom rules', function () {
        $generator = new BooleanValidatorGenerator();

        $rules = $generator->generate('active', [
            'type' => 'boolean',
            'rules' => ['in:0,1'],
        ]);

        expect($rules)->toBe([
            'active' => ['required', 'boolean', 'in:0,1'],
        ]);
    });

    it('generates boolean rule without required if nullable is true', function () {
        $generator = new BooleanValidatorGenerator();

        $rules = $generator->generate('active', [
            'type' => 'boolean',
            'nullable' => true,
        ]);

        expect($rules)->toBe([
            'active' => ['boolean'],
        ]);
    });

    it('returns empty array if type does not match', function () {
        $generator = new BooleanValidatorGenerator();

        $rules = $generator->generate('active', [
            'type' => 'float',
        ]);

        expect($rules)->toBe([]);
    });
});
