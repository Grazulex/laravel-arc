<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Fields\DecimalFieldGenerator;

it('generates float fields correctly', function () {
    $generator = new DecimalFieldGenerator();

    expect($generator->supports('decimal'))->toBeTrue();
    expect($generator->supports('string'))->toBeFalse();

    $code = $generator->generate('name', [
        'type' => 'decimal',
        'default' => '1.75',
    ]);

    expect($code)->toBe("public string \$name = '1.75';");

    $code = $generator->generate('name', [
        'type' => 'decimal',
    ]);

    expect($code)->toBe('public string $name;');
});
