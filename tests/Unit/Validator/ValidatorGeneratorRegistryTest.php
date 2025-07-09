<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Generator\ValidatorGeneratorRegistry;

describe('ValidatorGeneratorRegistry', function () {
    it('throws exception when invalid generator is provided', function () {
        expect(fn () => new ValidatorGeneratorRegistry(['invalid']))
            ->toThrow(InvalidArgumentException::class, 'Each generator must implement ValidatorGenerator.');
    });

    it('accepts valid generators', function () {
        $mockGenerator = mock(ValidatorGenerator::class);
        $registry = new ValidatorGeneratorRegistry([$mockGenerator]);

        expect($registry)->toBeInstanceOf(ValidatorGeneratorRegistry::class);
    });

    it('returns null when no generator supports the type', function () {
        $mockGenerator = mock(ValidatorGenerator::class);
        $mockGenerator->shouldReceive('supports')->with('unknown')->andReturn(false);

        $registry = new ValidatorGeneratorRegistry([$mockGenerator]);

        expect($registry->generate('field', 'unknown', []))->toBeNull();
    });

    it('returns validation rule when generator supports the type', function () {
        $mockGenerator = mock(ValidatorGenerator::class);
        $mockGenerator->shouldReceive('supports')->with('enum')->andReturn(true);
        $mockGenerator->shouldReceive('generate')->with('status', ['values' => ['active', 'inactive']])->andReturn('Rule::enum(StatusEnum::class)');

        $registry = new ValidatorGeneratorRegistry([$mockGenerator]);

        $result = $registry->generate('status', 'enum', ['values' => ['active', 'inactive']]);
        expect($result)->toBe('Rule::enum(StatusEnum::class)');
    });

    it('uses first matching generator when multiple support the type', function () {
        $firstGenerator = mock(ValidatorGenerator::class);
        $firstGenerator->shouldReceive('supports')->with('string')->andReturn(true);
        $firstGenerator->shouldReceive('generate')->with('name', ['max' => 255])->andReturn('string|max:255');

        $secondGenerator = mock(ValidatorGenerator::class);
        $secondGenerator->shouldReceive('supports')->with('string')->andReturn(true);
        // Second generator should not be called

        $registry = new ValidatorGeneratorRegistry([$firstGenerator, $secondGenerator]);

        $result = $registry->generate('name', 'string', ['max' => 255]);
        expect($result)->toBe('string|max:255');
    });
});
