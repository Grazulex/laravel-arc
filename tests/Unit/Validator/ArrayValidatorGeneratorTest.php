<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Validators\ArrayValidatorGenerator;

describe('ArrayValidatorGenerator', function () {
    it('supports array type', function () {
        $generator = new ArrayValidatorGenerator();

        expect($generator->supports('array'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates array rule with required and custom rules', function () {
        $generator = new ArrayValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('tags', [
            'type' => 'array',
            'required' => true,
            'rules' => ['min:1', 'max:5'],
        ], $context);

        expect($rules)->toBe([
            'tags' => ['array', 'required', 'min:1', 'max:5'],
        ]);
    });

    it('generates array rule without required if not specified', function () {
        $generator = new ArrayValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('tags', [
            'type' => 'array',
        ], $context);

        expect($rules)->toBe([
            'tags' => ['array', 'required'],
        ]);
    });

    it('returns empty array if type does not match', function () {
        $generator = new ArrayValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('tags', [
            'type' => 'string',
        ], $context);

        expect($rules)->toBe([]);
    });
});
