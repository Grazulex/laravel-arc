<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Fields\BooleanFieldGenerator;

it('generates boolean fields correctly', function () {
    $generator = new BooleanFieldGenerator();

    expect($generator->supports('bool'))->toBeTrue();
    expect($generator->supports('boolean'))->toBeTrue();
    expect($generator->supports('integer'))->toBeFalse();

    $code = $generator->generate('name', [
        'type' => 'boolean',
        'default' => True,
    ]);

    expect($code)->toBe("public bool \$name = true;");

    $code = $generator->generate('name', [
        'type' => 'bool',
    ]);

    expect($code)->toBe('public bool $name;');
});
