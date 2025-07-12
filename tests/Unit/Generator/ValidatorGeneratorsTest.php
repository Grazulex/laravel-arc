<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Validators\BooleanValidatorGenerator;
use Grazulex\LaravelArc\Generator\Validators\IntegerValidatorGenerator;
use Grazulex\LaravelArc\Generator\Validators\StringValidatorGenerator;

describe('Validator Generators', function () {
    it('supports correct types for string validator', function () {
        $generator = new StringValidatorGenerator();

        expect($generator->supports('string'))->toBe(true);
        expect($generator->supports('text'))->toBe(true);
        expect($generator->supports('integer'))->toBe(false);
        expect($generator->supports('boolean'))->toBe(false);
    });

    it('generates string validator rules correctly', function () {
        $generator = new StringValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('name', [
            'type' => 'string',
            'required' => true,
            'rules' => ['max:255'],
        ], $context);

        expect($rules)->toBe([
            'name' => ['string', 'required', 'max:255'],
        ]);

        $rules = $generator->generate('name', [
            'type' => 'string',
            'required' => false,
        ], $context);

        expect($rules)->toBe([
            'name' => ['string', 'nullable'],
        ]);
    });
});

describe('IntegerValidatorGenerator', function () {
    it('supports correct types for integer validator', function () {
        $generator = new IntegerValidatorGenerator();

        expect($generator->supports('int'))->toBe(true);
        expect($generator->supports('integer'))->toBe(true);
        expect($generator->supports('id'))->toBe(true);
        expect($generator->supports('string'))->toBe(false);
        expect($generator->supports('boolean'))->toBe(false);
    });

    it('generates integer validator rules correctly', function () {
        $generator = new IntegerValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('age', [
            'type' => 'integer',
            'required' => true,
            'rules' => ['min:0', 'max:120'],
        ], $context);

        expect($rules)->toBe([
            'age' => ['integer', 'required', 'min:0', 'max:120'],
        ]);

        $rules = $generator->generate('age', [
            'type' => 'int',
            'required' => false,
        ], $context);

        expect($rules)->toBe([
            'age' => ['integer', 'nullable'],
        ]);
    });
});

describe('BooleanValidatorGenerator', function () {
    it('supports correct types for boolean validator', function () {
        $generator = new BooleanValidatorGenerator();

        expect($generator->supports('bool'))->toBe(true);
        expect($generator->supports('boolean'))->toBe(true);
        expect($generator->supports('string'))->toBe(false);
        expect($generator->supports('integer'))->toBe(false);
    });

    it('generates boolean validator rules correctly', function () {
        $generator = new BooleanValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('active', [
            'type' => 'boolean',
            'required' => true,
        ], $context);

        expect($rules)->toBe([
            'active' => ['boolean', 'required'],
        ]);

        $rules = $generator->generate('active', [
            'type' => 'bool',
            'required' => false,
        ], $context);

        expect($rules)->toBe([
            'active' => ['boolean', 'nullable'],
        ]);
    });
});
