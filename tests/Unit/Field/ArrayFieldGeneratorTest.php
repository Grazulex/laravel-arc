<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Fields\ArrayFieldGenerator;

it('generates nullable array field with null default', function () {
    $generator = new ArrayFieldGenerator();

    $code = $generator->generate('items', [
        'nullable' => true,
    ]);

    expect($code)->toBe('public ?array $items = null;');
});

it('generates array field with default value', function () {
    $generator = new ArrayFieldGenerator();

    $code = $generator->generate('items', [
        'default' => ['a', 'b', 'c'],
    ]);

    expect($code)->toContain('public');
    expect($code)->toContain('$items =');
});
