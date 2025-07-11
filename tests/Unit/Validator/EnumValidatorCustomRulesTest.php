<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Validators\EnumValidatorGenerator;

describe('EnumValidatorGenerator - Custom Rules', function () {
    it('generates validation with custom enum_exists rule', function () {
        $generator = new EnumValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('status', [
            'type' => 'enum',
            'class' => 'Tests\\Fixtures\\Enums\\Status',
            'rules' => ['enum_exists'],
            'required' => true,
        ], $context);

        expect($rules)->toBe([
            'status' => ['enum:\\Tests\\Fixtures\\Enums\\Status', 'enum_exists:\\Tests\\Fixtures\\Enums\\Status', 'required'],
        ]);
    });

    it('generates validation with custom in_enum rule', function () {
        $generator = new EnumValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('priority', [
            'type' => 'enum',
            'class' => 'Tests\\Fixtures\\Enums\\Priority',
            'rules' => ['in_enum'],
            'required' => false,
        ], $context);

        expect($rules)->toBe([
            'priority' => ['enum:\\Tests\\Fixtures\\Enums\\Priority', 'in_enum:\\Tests\\Fixtures\\Enums\\Priority'],
        ]);
    });

    it('generates validation with multiple custom rules', function () {
        $generator = new EnumValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('category', [
            'type' => 'enum',
            'class' => 'Tests\\Fixtures\\Enums\\Status',
            'rules' => ['enum_exists', 'in_enum', 'nullable'],
            'required' => true,
        ], $context);

        expect($rules)->toBe([
            'category' => [
                'enum:\\Tests\\Fixtures\\Enums\\Status',
                'enum_exists:\\Tests\\Fixtures\\Enums\\Status',
                'in_enum:\\Tests\\Fixtures\\Enums\\Status',
                'required',
                'nullable',
            ],
        ]);
    });

    it('ignores custom enum rules for array-based enums', function () {
        $generator = new EnumValidatorGenerator();
        $context = new DtoGenerationContext();

        $rules = $generator->generate('status', [
            'type' => 'enum',
            'values' => ['draft', 'published'],
            'rules' => ['enum_exists', 'in_enum'], // Ces règles seront ignorées
            'required' => true,
        ], $context);

        expect($rules)->toBe([
            'status' => ['in:draft,published', 'required'],
        ]);
    });
});
