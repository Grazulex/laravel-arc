<?php

use Grazulex\LaravelArc\Generator\Fields\IntegerFieldGenerator;

it('generates integer fields correctly', function () {
    $generator = new IntegerFieldGenerator();

    expect($generator->supports('int'))->toBeTrue();
    expect($generator->supports('integer'))->toBeTrue();
    expect($generator->supports('string'))->toBeFalse();

    $code = $generator->generate('name', [
        'type' => 'int',
        'default' => 100,
    ]);

    expect($code)->toBe("public int \$name = 100;");

    $code = $generator->generate('name', [
        'type' => 'integer',
        'default' => 100,
    ]);

    expect($code)->toBe("public int \$name = 100;");

    $code = $generator->generate('name', [
        'type' => 'integer'
    ]);

    expect($code)->toBe("public int \$name = 0;");
});
