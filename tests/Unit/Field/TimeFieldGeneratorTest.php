<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Fields\TimeFieldGenerator;

it('supports time type', function () {
    $generator = new TimeFieldGenerator();

    expect($generator->supports('time'))->toBeTrue();
    expect($generator->supports('string'))->toBeFalse();
});

it('generates nullable time field with null default', function () {
    $generator = new TimeFieldGenerator();

    $code = $generator->generate('alarm', [
        'nullable' => true,
    ]);

    expect($code)->toBe('public ?\\Carbon\\Carbon $alarm = null;');
});
