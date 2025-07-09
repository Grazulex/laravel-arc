<?php

use Grazulex\LaravelArc\Generator\FieldGeneratorRegistry;
use Grazulex\LaravelArc\Generator\Fields\StringFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\IntegerFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\FloatFieldGenerator;

it('calls the correct generator for a string field', function () {
    $registry = new FieldGeneratorRegistry([
        new StringFieldGenerator(),
    ]);

    $result = $registry->generate('name', ['type' => 'string', 'default' => "'test'"]);

    expect($result)->toBe("public string \$name = 'test';");
});

it('calls the correct generator for an integer field', function () {
    $registry = new FieldGeneratorRegistry([
        new IntegerFieldGenerator(),
    ]);

    $result = $registry->generate('age', ['type' => 'integer', 'default' => 30]);

    expect($result)->toBe("public int \$age = 30;");
});

it('calls the correct generator for a float field', function () {
    $registry = new FieldGeneratorRegistry([
        new FloatFieldGenerator(),
    ]);

    $result = $registry->generate('height', ['type' => 'float', 'default' => 1.75]);

    expect($result)->toBe("public float \$height = 1.75;");
});

it('throws if no generator supports the field type', function () {
    $registry = new FieldGeneratorRegistry([]);


    $registry->generate('age', ['type' => 'long', 'default' => 3.14]);
})->throws(InvalidArgumentException::class, "No generator found for field type 'long'");
