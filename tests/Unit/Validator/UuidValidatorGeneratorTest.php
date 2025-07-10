<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Validators\UuidValidatorGenerator;

describe('UuidValidatorGenerator', function () {
    it('supports uuid type', function () {
        $generator = new UuidValidatorGenerator();

        expect($generator->supports('uuid'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates uuid rule with required and extras', function () {
        $generator = new UuidValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('user_id', [
            'type' => 'uuid',
            'required' => true,
            'rules' => ['exists:users,id'],
        ], $context);

        expect($rules)->toBe([
            'user_id' => ['uuid', 'required', 'exists:users,id'],
        ]);
    });

    it('generates uuid rule without required if not specified', function () {
        $generator = new UuidValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('reference', [
            'type' => 'uuid',
        ], $context);

        expect($rules)->toBe([
            'reference' => ['uuid', 'required'],
        ]);
    });

    it('returns empty array if type does not match', function () {
        $generator = new UuidValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('reference', ['type' => 'string'], $context);

        expect($rules)->toBe([]);
    });
});
