<?php

use Grazulex\LaravelArc\Generator\FieldGeneratorRegistry;
use Grazulex\LaravelArc\Generator\Fields\StringFieldGenerator;

it('calls the correct generator for a string field', function () {
    $registry = new FieldGeneratorRegistry([
        new StringFieldGenerator(),
    ]);

    $result = $registry->generate('name', ['type' => 'string', 'default' => "'test'"]);

    expect($result)->toBe("public string \$name = 'test';");
});

it('throws if no generator supports the field type', function () {
    $registry = new FieldGeneratorRegistry([]);

    $registry->generate('age', ['type' => 'integer']);
})->throws(InvalidArgumentException::class, "No generator found for field type 'integer'");
