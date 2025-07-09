<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Fields\FloatFieldGenerator;

it('generates float fields correctly', function () {
    $generator = new FloatFieldGenerator();

    expect($generator->supports('float'))->toBeTrue();
    expect($generator->supports('double'))->toBeTrue();
    expect($generator->supports('string'))->toBeFalse();

    $code = $generator->generate('name', [
        'type' => 'double',
        'default' => '1.75',
    ]);

    expect($code)->toBe('public float $name = 1.75;');

    $code = $generator->generate('name', [
        'type' => 'float',
        'default' => '1.75',
    ]);

    expect($code)->toBe('public float $name = 1.75;');

    $code = $generator->generate('name', [
        'type' => 'float',
    ]);

    expect($code)->toBe('public float $name;');
});
