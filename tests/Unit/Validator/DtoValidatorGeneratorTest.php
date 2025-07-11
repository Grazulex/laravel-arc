<?php

declare(strict_types=1);

namespace Tests\Unit\Validator;

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Validators\DtoValidatorGenerator;

it('DtoValidatorGenerator → it supports dto type', function () {
    $generator = new DtoValidatorGenerator();

    expect($generator->supports('dto'))->toBeTrue();
    expect($generator->supports('string'))->toBeFalse();
});

it('DtoValidatorGenerator → it generates array rule with required', function () {
    $generator = new DtoValidatorGenerator();
    $context = new DtoGenerationContext();

    $definition = [
        'type' => 'dto',
        'dto' => 'UserDTO',
        'required' => true,
    ];

    $result = $generator->generate('author', $definition, $context);

    expect($result)->toHaveKey('author');
    expect($result['author'])->toContain('array');
    expect($result['author'])->toContain('required');
});

it('DtoValidatorGenerator → it generates array rule without required', function () {
    $generator = new DtoValidatorGenerator();
    $context = new DtoGenerationContext();

    $definition = [
        'type' => 'dto',
        'dto' => 'UserDTO',
        'required' => false,
    ];

    $result = $generator->generate('author', $definition, $context);

    expect($result)->toHaveKey('author');
    expect($result['author'])->toContain('array');
    expect($result['author'])->not->toContain('required');
});

it('DtoValidatorGenerator → it merges custom rules', function () {
    $generator = new DtoValidatorGenerator();
    $context = new DtoGenerationContext();

    $definition = [
        'type' => 'dto',
        'dto' => 'UserDTO',
        'required' => true,
        'rules' => ['min:1', 'max:10'],
    ];

    $result = $generator->generate('author', $definition, $context);

    expect($result)->toHaveKey('author');
    expect($result['author'])->toContain('array');
    expect($result['author'])->toContain('required');
    expect($result['author'])->toContain('min:1');
    expect($result['author'])->toContain('max:10');
});
