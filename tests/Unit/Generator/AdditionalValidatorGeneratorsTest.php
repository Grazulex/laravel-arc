<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Validators\BooleanValidatorGenerator;
use Grazulex\LaravelArc\Generator\Validators\IntegerValidatorGenerator;

describe('Additional Validator Generators', function () {
    beforeEach(function () {
        $this->context = new DtoGenerationContext();
    });

    describe('BooleanValidatorGenerator', function () {
        beforeEach(function () {
            $this->generator = new BooleanValidatorGenerator();
        });

        it('supports boolean type', function () {
            expect($this->generator->supports('boolean'))->toBe(true);
            expect($this->generator->supports('string'))->toBe(false);
        });

        it('generates boolean validation rules', function () {
            $config = ['type' => 'boolean', 'required' => true];
            $result = $this->generator->generate('is_active', $config, $this->context);

            expect($result)->toEqual(['is_active' => ['boolean', 'required']]);
        });

        it('generates boolean validation rules without required', function () {
            $config = ['type' => 'boolean', 'required' => false];
            $result = $this->generator->generate('is_active', $config, $this->context);

            expect($result)->toEqual(['is_active' => ['boolean', 'nullable']]);
        });

        it('returns empty array for non-matching type', function () {
            $config = ['type' => 'string'];
            $result = $this->generator->generate('name', $config, $this->context);

            expect($result)->toEqual([]);
        });
    });

    describe('IntegerValidatorGenerator', function () {
        beforeEach(function () {
            $this->generator = new IntegerValidatorGenerator();
        });

        it('supports integer type', function () {
            expect($this->generator->supports('integer'))->toBe(true);
            expect($this->generator->supports('string'))->toBe(false);
        });

        it('generates integer validation rules', function () {
            $config = ['type' => 'integer', 'required' => true];
            $result = $this->generator->generate('age', $config, $this->context);

            expect($result)->toEqual(['age' => ['integer', 'required']]);
        });

        it('generates integer validation rules without required', function () {
            $config = ['type' => 'integer', 'required' => false];
            $result = $this->generator->generate('age', $config, $this->context);

            expect($result)->toEqual(['age' => ['integer', 'nullable']]);
        });

        it('returns empty array for non-matching type', function () {
            $config = ['type' => 'string'];
            $result = $this->generator->generate('name', $config, $this->context);

            expect($result)->toEqual([]);
        });
    });
});
