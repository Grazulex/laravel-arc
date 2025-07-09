<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Contracts\FieldGenerator;
use Grazulex\LaravelArc\Generator\FieldGeneratorRegistry;
use Grazulex\LaravelArc\Generator\Fields\BooleanFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\DecimalFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\FloatFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\IntegerFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\StringFieldGenerator;

describe('FieldGeneratorRegistry', function () {
    it('throws exception when invalid generator is provided', function () {
        expect(fn () => new FieldGeneratorRegistry(['invalid']))
            ->toThrow(InvalidArgumentException::class, 'Each generator must implement FieldGenerator.');
    });

    it('accepts valid generators', function () {
        $mockGenerator = mock(FieldGenerator::class);
        $registry = new FieldGeneratorRegistry([$mockGenerator]);

        expect($registry)->toBeInstanceOf(FieldGeneratorRegistry::class);
    });

    it('calls the correct generator for a string field', function () {
        $registry = new FieldGeneratorRegistry([
            new StringFieldGenerator(),
        ]);

        $result = $registry->generate('name', ['type' => 'string', 'default' => 'test']);

        expect($result)->toBe("public string \$name = 'test';");
    });

    it('calls the correct generator for an integer field', function () {
        $registry = new FieldGeneratorRegistry([
            new IntegerFieldGenerator(),
        ]);

        $result = $registry->generate('age', ['type' => 'integer', 'default' => 30]);

        expect($result)->toBe('public int $age = 30;');
    });

    it('calls the correct generator for a float field', function () {
        $registry = new FieldGeneratorRegistry([
            new FloatFieldGenerator(),
        ]);

        $result = $registry->generate('height', ['type' => 'float', 'default' => 1.75]);

        expect($result)->toBe('public float $height = 1.75;');
    });

    it('calls the correct generator for a double field', function () {
        $registry = new FieldGeneratorRegistry([
            new FloatFieldGenerator(),
        ]);

        $result = $registry->generate('weight', ['type' => 'double', 'default' => 70.5]);

        expect($result)->toBe('public float $weight = 70.5;');
    });

    it('calls the correct generator for a decimal field', function () {
        $registry = new FieldGeneratorRegistry([
            new DecimalFieldGenerator(),
        ]);

        $result = $registry->generate('price', ['type' => 'decimal', 'default' => '19.99']);

        expect($result)->toBe("public string \$price = '19.99';");
    });

    it('calls the correct generator for a boolean field', function () {
        $registry = new FieldGeneratorRegistry([
            new BooleanFieldGenerator(),
        ]);

        $result = $registry->generate('is_active', ['type' => 'boolean', 'default' => true]);

        expect($result)->toBe('public bool $is_active = true;');
    });

    it('throws if no generator supports the field type', function () {
        $registry = new FieldGeneratorRegistry([]);

        $registry->generate('age', ['type' => 'long', 'default' => 3.14]);
    })->throws(InvalidArgumentException::class, "No generator found for field type 'long'");
});
