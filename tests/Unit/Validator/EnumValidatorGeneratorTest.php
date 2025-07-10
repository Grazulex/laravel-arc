<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Validators\EnumValidatorGenerator;

describe('EnumValidatorGenerator', function () {
    it('supports enum type', function () {
        $generator = new EnumValidatorGenerator();

        expect($generator->supports('enum'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('returns empty array if values are missing or invalid', function () {
        $generator = new EnumValidatorGenerator();
        $context = new DtoGenerationContext();

        expect($generator->generate('status', ['type' => 'enum'], $context))->toBe([]);
        expect($generator->generate('status', ['type' => 'enum', 'values' => null], $context))->toBe([]);
        expect($generator->generate('status', ['type' => 'string'], $context))->toBe([]);
    });

    it('generates enum rule with required', function () {
        $generator = new EnumValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('status', [
            'type' => 'enum',
            'required' => true,
            'values' => ['draft', 'published', 'archived'],
        ], $context);

        expect($rules)->toBe([
            'status' => ['in:draft,published,archived', 'required'],
        ]);
    });

    it('generates enum rule without required if not defined', function () {
        $generator = new EnumValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('status', [
            'type' => 'enum',
            'values' => ['draft', 'published'],
        ], $context);

        expect($rules)->toBe([
            'status' => ['in:draft,published', 'required'],
        ]);
    });
});
