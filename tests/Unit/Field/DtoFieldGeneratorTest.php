<?php

declare(strict_types=1);

namespace Tests\Unit\Field;

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Fields\DtoFieldGenerator;

it('DtoFieldGenerator → it supports dto type', function () {
    $generator = new DtoFieldGenerator();

    expect($generator->supports('dto'))->toBeTrue();
    expect($generator->supports('string'))->toBeFalse();
});

it('DtoFieldGenerator → it generates dto field when nesting allowed', function () {
    $generator = new DtoFieldGenerator();
    $context = new DtoGenerationContext();

    $definition = [
        'dto' => 'UserDTO',
        'required' => true,
    ];

    $result = $generator->generate('author', $definition, $context);

    expect($result)->toContain('public readonly \\UserDTO $author,');
});

it('DtoFieldGenerator → it generates nullable dto field when not required', function () {
    $generator = new DtoFieldGenerator();
    $context = new DtoGenerationContext();

    $definition = [
        'dto' => 'UserDTO',
        'required' => false,
    ];

    $result = $generator->generate('author', $definition, $context);

    expect($result)->toContain('public readonly ?\\UserDTO $author,');
});

it('DtoFieldGenerator → it prevents infinite nesting', function () {
    $generator = new DtoFieldGenerator();
    $context = new DtoGenerationContext(1); // Max depth 1

    // Simulate being at max depth
    $context->enterDto('UserDTO');

    $definition = [
        'dto' => 'UserDTO',
        'required' => true,
    ];

    $result = $generator->generate('author', $definition, $context);

    // Should fallback to array when nesting not allowed
    expect($result)->toContain('public readonly array $author,');
});

it('DtoFieldGenerator → it prevents circular references', function () {
    $generator = new DtoFieldGenerator();
    $context = new DtoGenerationContext();

    // Simulate circular reference
    $context->enterDto('UserDTO');

    $definition = [
        'dto' => 'UserDTO', // Same as current DTO
        'required' => true,
    ];

    $result = $generator->generate('author', $definition, $context);

    // Should fallback to array when circular reference detected
    expect($result)->toContain('public readonly array $author,');
});
