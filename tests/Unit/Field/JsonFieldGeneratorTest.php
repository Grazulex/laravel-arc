<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Fields\JsonFieldGenerator;

it('supports json type', function () {
    $generator = new JsonFieldGenerator();

    expect($generator->supports('json'))->toBeTrue();
    expect($generator->supports('string'))->toBeFalse();
});

it('generates nullable json field with null default', function () {
    $generator = new JsonFieldGenerator();

    $code = $generator->generate('metadata', [
        'nullable' => true,
    ]);

    expect($code)->toBe('public ?array $metadata = null;');
});

it('generates json field with default value', function () {
    $generator = new JsonFieldGenerator();

    $code = $generator->generate('metadata', [
        'default' => ['key' => 'value'],
    ]);

    expect($code)->toContain('public');
    expect($code)->toContain('$metadata =');
});
