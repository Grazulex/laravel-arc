<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Fields\DateTimeFieldGenerator;

it('supports datetime type', function () {
    $generator = new DateTimeFieldGenerator();

    expect($generator->supports('datetime'))->toBeTrue();
    expect($generator->supports('string'))->toBeFalse();
});

it('generates nullable datetime field with null default', function () {
    $generator = new DateTimeFieldGenerator();

    $code = $generator->generate('published_at', [
        'nullable' => true,
    ]);

    expect($code)->toBe('public ?\\Carbon\\Carbon $published_at = null;');
});

it('generates non-nullable datetime field without default', function () {
    $generator = new DateTimeFieldGenerator();

    $code = $generator->generate('updated_at', [
        'nullable' => false,
    ]);

    expect($code)->toBe('public \\Carbon\\Carbon $updated_at;');
});

it('ignores string default value for datetime', function () {
    $generator = new DateTimeFieldGenerator();

    $code = $generator->generate('scheduled_at', [
        'default' => '2024-07-09 13:00:00',
        'nullable' => false,
    ]);

    expect($code)->toBe('public \\Carbon\\Carbon $scheduled_at;');
});

it('handles explicit null default for datetime', function () {
    $generator = new DateTimeFieldGenerator();

    $code = $generator->generate('deleted_at', [
        'default' => null,
        'nullable' => true,
    ]);

    expect($code)->toBe('public ?\\Carbon\\Carbon $deleted_at = null;');
});
