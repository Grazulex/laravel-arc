<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Fields\UuidFieldGenerator;

it('generates nullable uuid field with null default', function () {
    $generator = new UuidFieldGenerator();

    $code = $generator->generate('uuid', [
        'nullable' => true,
    ]);

    expect($code)->toBe('public ?string $uuid = null;');
});

it('generates uuid field with default value', function () {
    $generator = new UuidFieldGenerator();

    $code = $generator->generate('uuid', [
        'default' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
    ]);

    expect($code)->toContain('public');
    expect($code)->toContain('$uuid =');
});
