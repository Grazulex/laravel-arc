<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\EnumValidatorGenerator;

it('generates a rule for enum values', function () {
    $generator = new EnumValidatorGenerator();

    $rule = $generator->generate('status', [
        'type' => 'enum',
        'values' => ['draft', 'published'],
    ]);

    expect($rule)->toBe('in:draft,published');
});

it('generates a rule for PHP enum class', function () {
    $generator = new EnumValidatorGenerator();

    $rule = $generator->generate('status', [
        'type' => 'enum',
        'enum' => 'App\\Enums\\PostStatus',
    ]);

    expect($rule)->toBe('Rule::enum(App\\Enums\\PostStatus::class)');
});
