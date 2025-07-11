<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Fields\EnumFieldGenerator;

describe('EnumFieldGenerator', function () {
    it('supports enum type', function () {
        $generator = new EnumFieldGenerator();

        expect($generator->supports('enum'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates non-required enum field with null default', function () {
        $generator = new EnumFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('status', [
            'required' => false,
        ], $context);

        expect($code)->toBe('public ?string $status = null;');
    });

    it('generates enum field with default value', function () {
        $generator = new EnumFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('status', [
            'default' => 'draft',
        ], $context);

        expect($code)->toContain('public');
        expect($code)->toContain('$status =');
    });

    it('generates enum field with PHP enum class', function () {
        $generator = new EnumFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('status', [
            'type' => 'enum',
            'class' => 'Tests\\Fixtures\\Enums\\Status',
            'required' => true,
        ], $context);

        expect($code)->toBe('public \\Tests\\Fixtures\\Enums\\Status $status;');
    });

    it('generates nullable enum field with PHP enum class', function () {
        $generator = new EnumFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('status', [
            'type' => 'enum',
            'class' => 'Tests\\Fixtures\\Enums\\Status',
            'required' => false,
        ], $context);

        expect($code)->toBe('public ?\\Tests\\Fixtures\\Enums\\Status $status = null;');
    });

    it('generates enum field with PHP enum class and default value', function () {
        $generator = new EnumFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('status', [
            'type' => 'enum',
            'class' => 'Tests\\Fixtures\\Enums\\Status',
            'default' => 'draft',
            'required' => true,
        ], $context);

        expect($code)->toBe('public \\Tests\\Fixtures\\Enums\\Status $status = \\Tests\\Fixtures\\Enums\\Status::DRAFT;');
    });

    it('generates enum field with PHP enum class and explicit case reference', function () {
        $generator = new EnumFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('status', [
            'type' => 'enum',
            'class' => 'Tests\\Fixtures\\Enums\\Status',
            'default' => 'Tests\\Fixtures\\Enums\\Status::PUBLISHED',
            'required' => true,
        ], $context);

        expect($code)->toBe('public \\Tests\\Fixtures\\Enums\\Status $status = Tests\\Fixtures\\Enums\\Status::PUBLISHED;');
    });
});
