<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\EnumValidatorGenerator;

describe('EnumValidatorGenerator', function () {
    it('supports enum type', function () {
        $generator = new EnumValidatorGenerator();

        expect($generator->supports('enum'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('returns empty array if values are missing or not an array', function () {
        $generator = new EnumValidatorGenerator();

        expect($generator->generate('status', ['type' => 'enum']))->toBe([]);
        expect($generator->generate('status', ['type' => 'enum', 'values' => null]))->toBe([]);
        expect($generator->generate('status', ['type' => 'string']))->toBe([]);
    });

    it('generates enum rule with required by default', function () {
        $generator = new EnumValidatorGenerator();

        $rules = $generator->generate('status', [
            'type' => 'enum',
            'values' => ['draft', 'published'],
        ]);

        expect($rules)->toBe([
            'status' => ['required', 'in:draft,published'],
        ]);
    });

    it('generates enum rule without required if nullable is true', function () {
        $generator = new EnumValidatorGenerator();

        $rules = $generator->generate('status', [
            'type' => 'enum',
            'values' => ['draft', 'published'],
            'nullable' => true,
        ]);

        expect($rules)->toBe([
            'status' => ['in:draft,published'],
        ]);
    });
});
