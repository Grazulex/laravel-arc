<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\EnumValidatorGenerator;

describe('EnumValidatorGenerator', function () {
    it('supports enum type', function () {
        $generator = new EnumValidatorGenerator();

        expect($generator->supports('enum'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('returns empty array if values are missing or invalid', function () {
        $generator = new EnumValidatorGenerator();

        expect($generator->generate('status', ['type' => 'enum']))->toBe([]);
        expect($generator->generate('status', ['type' => 'enum', 'values' => null]))->toBe([]);
        expect($generator->generate('status', ['type' => 'string']))->toBe([]);
    });

    it('generates enum rule with required', function () {
        $generator = new EnumValidatorGenerator();

        $rules = $generator->generate('status', [
            'type' => 'enum',
            'required' => true,
            'values' => ['draft', 'published', 'archived'],
        ]);

        expect($rules)->toBe([
            'status' => ['in:draft,published,archived', 'required'],
        ]);
    });

    it('generates enum rule without required if not defined', function () {
        $generator = new EnumValidatorGenerator();

        $rules = $generator->generate('status', [
            'type' => 'enum',
            'values' => ['draft', 'published'],
        ]);

        expect($rules)->toBe([
            'status' => ['in:draft,published', 'required'],
        ]);
    });
});
