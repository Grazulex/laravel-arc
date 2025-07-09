<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\UuidValidatorGenerator;

describe('UuidValidatorGenerator', function () {
    it('supports uuid type', function () {
        $generator = new UuidValidatorGenerator();

        expect($generator->supports('uuid'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates uuid rule with required', function () {
        $generator = new UuidValidatorGenerator();

        $rules = $generator->generate('uuid', [
            'type' => 'uuid',
            'required' => true,
        ]);

        expect($rules)->toBe([
            'uuid' => ['uuid', 'required'],
        ]);
    });

    it('returns only uuid rule when no extras given', function () {
        $generator = new UuidValidatorGenerator();

        $rules = $generator->generate('uuid', ['type' => 'uuid']);

        expect($rules)->toBe([
            'uuid' => ['uuid'],
        ]);
    });
});
