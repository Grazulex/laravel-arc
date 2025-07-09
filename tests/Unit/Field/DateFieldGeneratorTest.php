<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Fields\DateFieldGenerator;

it('supports date type', function () {
    $generator = new DateFieldGenerator();

    expect($generator->supports('date'))->toBeTrue();
    expect($generator->supports('string'))->toBeFalse();
});

it('generates nullable date field with null default', function () {
    $generator = new DateFieldGenerator();

    $code = $generator->generate('birth_date', [
        'nullable' => true,
    ]);

    expect($code)->toBe('public ?\\Carbon\\Carbon $birth_date = null;');
});

it('generates non-nullable date field without default', function () {
    $generator = new DateFieldGenerator();

    $code = $generator->generate('created_on', [
        'nullable' => false,
    ]);

    expect($code)->toBe('public \\Carbon\\Carbon $created_on;');
});

it('ignores string default value for date', function () {
    $generator = new DateFieldGenerator();

    $code = $generator->generate('start_date', [
        'default' => '2024-01-01',
        'nullable' => false,
    ]);

    expect($code)->toBe('public \\Carbon\\Carbon $start_date;');
});

it('handles explicit null default for date', function () {
    $generator = new DateFieldGenerator();

    $code = $generator->generate('end_date', [
        'default' => null,
        'nullable' => true,
    ]);

    expect($code)->toBe('public ?\\Carbon\\Carbon $end_date = null;');
});
