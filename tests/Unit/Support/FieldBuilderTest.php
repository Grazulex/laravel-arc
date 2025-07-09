<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\FieldBuilder;

it('generates string field with default', function () {
    $code = FieldBuilder::generate('name', 'string', [
        'default' => 'John',
    ]);
    expect($code)->toBe("public string \$name = 'John';");
});

it('generates nullable integer without default', function () {
    $code = FieldBuilder::generate('age', 'integer', [
        'nullable' => true,
    ]);
    expect($code)->toBe('public ?int $age = null;');
});

it('generates boolean with false default', function () {
    $code = FieldBuilder::generate('active', 'boolean', [
        'default' => false,
    ]);
    expect($code)->toBe('public bool $active = false;');
});

it('generates decimal as string default', function () {
    $code = FieldBuilder::generate('price', 'decimal', [
        'default' => '9.99',
    ]);
    expect($code)->toBe("public string \$price = '9.99';");
});

it('generates json array', function () {
    $code = FieldBuilder::generate('meta', 'json', [
        'default' => ['a' => 1, 'b' => true],
    ]);
    expect($code)->toContain('public array $meta =');
});
