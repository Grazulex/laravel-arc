<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Validators\BooleanValidatorGenerator;

describe('BooleanValidatorGenerator', function () {
    it('supports boolean type', function () {
        $generator = new BooleanValidatorGenerator();

        expect($generator->supports('boolean'))->toBeTrue();
        expect($generator->supports('integer'))->toBeFalse();
    });

    it('generates boolean rule with required and extras', function () {
        $generator = new BooleanValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('is_active', [
            'type' => 'boolean',
            'required' => true,
            'rules' => ['in:0,1'],
        ], $context);

        expect($rules)->toBe([
            'is_active' => ['boolean', 'required', 'in:0,1'],
        ]);
    });

    it('generates rule without required if not set', function () {
        $generator = new BooleanValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('is_active', [
            'type' => 'boolean',
        ], $context);

        expect($rules)->toBe([
            'is_active' => ['boolean', 'required'],
        ]);
    });

    it('returns empty array if type is incorrect', function () {
        $generator = new BooleanValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('is_active', [
            'type' => 'string',
        ], $context);

        expect($rules)->toBe([]);
    });
});
