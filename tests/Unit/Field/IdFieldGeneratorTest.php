<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Fields\IdFieldGenerator;

it('supports id type', function () {
    $generator = new IdFieldGenerator();

    expect($generator->supports('id'))->toBeTrue();
    expect($generator->supports('string'))->toBeFalse();
});

it('generates nullable id field with null default', function () {
    $generator = new IdFieldGenerator();

    $code = $generator->generate('user_id', [
        'nullable' => true,
    ]);

    expect($code)->toBe('public ?string $user_id = null;');
});

it('generates id field with default value', function () {
    $generator = new IdFieldGenerator();

    $code = $generator->generate('user_id', [
        'default' => 42,
    ]);

    expect($code)->toContain('public');
    expect($code)->toContain('$user_id =');
});
