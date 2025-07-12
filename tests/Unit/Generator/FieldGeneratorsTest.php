<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Fields\ArrayFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\BooleanFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\FloatFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\IntegerFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\StringFieldGenerator;

describe('Field Generators', function () {
    it('supports correct types for string generator', function () {
        $generator = new StringFieldGenerator();

        expect($generator->supports('string'))->toBe(true);
        expect($generator->supports('integer'))->toBe(false);
        expect($generator->supports('boolean'))->toBe(false);
    });

    it('generates string field code correctly', function () {
        $generator = new StringFieldGenerator();
        $context = new DtoGenerationContext();

        $result = $generator->generate('name', ['type' => 'string', 'default' => 'John'], $context);
        expect($result)->toBe("public string \$name = 'John';");

        $result = $generator->generate('name', ['type' => 'string'], $context);
        expect($result)->toBe('public string $name;');
    });

    it('supports correct types for integer generator', function () {
        $generator = new IntegerFieldGenerator();

        expect($generator->supports('int'))->toBe(true);
        expect($generator->supports('integer'))->toBe(true);
        expect($generator->supports('string'))->toBe(false);
        expect($generator->supports('boolean'))->toBe(false);
    });

    it('generates integer field code correctly', function () {
        $generator = new IntegerFieldGenerator();
        $context = new DtoGenerationContext();

        $result = $generator->generate('age', ['type' => 'int', 'default' => 25], $context);
        expect($result)->toBe('public int $age = 25;');

        $result = $generator->generate('age', ['type' => 'integer'], $context);
        expect($result)->toBe('public int $age;');
    });

    it('supports correct types for boolean generator', function () {
        $generator = new BooleanFieldGenerator();

        expect($generator->supports('bool'))->toBe(true);
        expect($generator->supports('boolean'))->toBe(true);
        expect($generator->supports('string'))->toBe(false);
        expect($generator->supports('integer'))->toBe(false);
    });

    it('generates boolean field code correctly', function () {
        $generator = new BooleanFieldGenerator();
        $context = new DtoGenerationContext();

        $result = $generator->generate('active', ['type' => 'bool', 'default' => true], $context);
        expect($result)->toBe('public bool $active = true;');

        $result = $generator->generate('active', ['type' => 'boolean'], $context);
        expect($result)->toBe('public bool $active;');
    });

    it('supports correct types for float generator', function () {
        $generator = new FloatFieldGenerator();

        expect($generator->supports('float'))->toBe(true);
        expect($generator->supports('double'))->toBe(true);
        expect($generator->supports('string'))->toBe(false);
        expect($generator->supports('integer'))->toBe(false);
    });

    it('generates float field code correctly', function () {
        $generator = new FloatFieldGenerator();
        $context = new DtoGenerationContext();

        $result = $generator->generate('price', ['type' => 'float', 'default' => 10.5], $context);
        expect($result)->toBe('public float $price = 10.5;');

        $result = $generator->generate('price', ['type' => 'double'], $context);
        expect($result)->toBe('public float $price;');
    });

    it('supports correct types for array generator', function () {
        $generator = new ArrayFieldGenerator();

        expect($generator->supports('array'))->toBe(true);
        expect($generator->supports('string'))->toBe(false);
        expect($generator->supports('integer'))->toBe(false);
    });

    it('generates array field code correctly', function () {
        $generator = new ArrayFieldGenerator();
        $context = new DtoGenerationContext();

        $result = $generator->generate('tags', ['type' => 'array', 'default' => []], $context);
        expect($result)->toBe("public array \$tags = array (\n);");

        $result = $generator->generate('tags', ['type' => 'array'], $context);
        expect($result)->toBe('public array $tags;');
    });
});
