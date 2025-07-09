<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Fields\StringFieldGenerator;

it('generates string fields correctly', function () {
    $generator = new StringFieldGenerator();

    expect($generator->supports('string'))->toBeTrue();
    expect($generator->supports('integer'))->toBeFalse();

    $code = $generator->generate('name', [
        'type' => 'string',
        'default' => "'John'",
    ]);

    expect($code)->toBe("public string \$name = 'John';");

    $code = $generator->generate('name', [
        'type' => 'string',
    ]);

    expect($code)->toBe("public string \$name = '';");
});
