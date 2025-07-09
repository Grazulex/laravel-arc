<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\EnumValidatorGenerator;

describe('EnumValidatorGenerator', function () {
    it('supports enum type', function () {
        $generator = new EnumValidatorGenerator();

        expect($generator->supports('enum'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates a rule for enum values', function () {
        $generator = new EnumValidatorGenerator();

        $rules = $generator->generate('status', [
            'type' => 'enum',
            'values' => ['draft', 'published'],
            'rules' => ['required'],
        ]);

        expect($rules)->toBe([
            'status' => ['required', 'in:draft,published'],
        ]);
    });

    it('returns rule only for in: when no extra rules given', function () {
        $generator = new EnumValidatorGenerator();

        $rules = $generator->generate('status', [
            'type' => 'enum',
            'values' => ['draft', 'published'],
        ]);

        expect($rules)->toBe([
            'status' => ['in:draft,published'],
        ]);
    });

    it('returns empty array when no values or enum given', function () {
        $generator = new EnumValidatorGenerator();

        $rules = $generator->generate('status', [
            'type' => 'enum',
        ]);

        expect($rules)->toBe([
            'status' => [],
        ]);
    });
});
