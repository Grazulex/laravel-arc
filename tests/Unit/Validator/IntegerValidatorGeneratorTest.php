<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\IntegerValidatorGenerator;

describe('IntegerValidatorGenerator', function () {
    it('supports integer type', function () {
        $generator = new IntegerValidatorGenerator();

        expect($generator->supports('integer'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates integer rule with required and custom rules', function () {
        $generator = new IntegerValidatorGenerator();

        $rules = $generator->generate('age', [
            'type' => 'integer',
            'required' => true,
            'rules' => ['min:18'],
        ]);

        expect($rules)->toBe([
            'age' => ['integer', 'required', 'min:18'],
        ]);
    });

    it('returns only integer rule when no extras given', function () {
        $generator = new IntegerValidatorGenerator();

        $rules = $generator->generate('age', ['type' => 'integer']);

        expect($rules)->toBe([
            'age' => ['integer'],
        ]);
    });
});
