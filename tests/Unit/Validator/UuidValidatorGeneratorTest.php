<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\UuidValidatorGenerator;

describe('UuidValidatorGenerator', function () {
    it('supports uuid type', function () {
        $generator = new UuidValidatorGenerator();

        expect($generator->supports('uuid'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates uuid rule with required and extras', function () {
        $generator = new UuidValidatorGenerator();

        $rules = $generator->generate('uuid', [
            'type' => 'uuid',
            'rules' => ['exists:users,id'],
        ]);

        expect($rules)->toBe([
            'uuid' => ['required', 'uuid', 'exists:users,id'],
        ]);
    });

    it('generates uuid rule without required if nullable is true', function () {
        $generator = new UuidValidatorGenerator();

        $rules = $generator->generate('uuid', [
            'type' => 'uuid',
            'nullable' => true,
        ]);

        expect($rules)->toBe([
            'uuid' => ['uuid'],
        ]);
    });

    it('returns empty array if type does not match', function () {
        $generator = new UuidValidatorGenerator();

        $rules = $generator->generate('uuid', ['type' => 'string']);

        expect($rules)->toBe([]);
    });
});
