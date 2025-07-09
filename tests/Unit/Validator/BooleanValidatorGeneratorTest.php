<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\BooleanValidatorGenerator;

describe('BooleanValidatorGenerator', function () {
    it('supports boolean type', function () {
        $generator = new BooleanValidatorGenerator();

        expect($generator->supports('boolean'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates boolean rule with required', function () {
        $generator = new BooleanValidatorGenerator();

        $rules = $generator->generate('flag', [
            'type' => 'boolean',
            'required' => true,
        ]);

        expect($rules)->toBe([
            'flag' => ['boolean', 'required'],
        ]);
    });

    it('returns only boolean rule when no extras given', function () {
        $generator = new BooleanValidatorGenerator();

        $rules = $generator->generate('flag', ['type' => 'boolean']);

        expect($rules)->toBe([
            'flag' => ['boolean'],
        ]);
    });
});
