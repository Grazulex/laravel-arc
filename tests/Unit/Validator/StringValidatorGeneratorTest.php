<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Validators\StringValidatorGenerator;

describe('StringValidatorGenerator', function () {
    it('supports string type', function () {
        $generator = new StringValidatorGenerator();

        expect($generator->supports('string'))->toBeTrue();
        expect($generator->supports('integer'))->toBeFalse();
    });

    it('generates a string rule with required and custom rules', function () {
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
    });

    it('returns only string rule when no extras given', function () {
        $generator = new StringValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('name', ['type' => 'integer'], $context);

        expect($rules)->toBe([]);
    });
});
