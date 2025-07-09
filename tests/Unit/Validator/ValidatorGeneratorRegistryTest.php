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

    it('returns empty array when no generator supports the type', function () {
        $mockGenerator = mock(ValidatorGenerator::class);
        $mockGenerator->shouldReceive('supports')->with('unknown')->andReturn(false);

        $registry = new ValidatorGeneratorRegistry([$mockGenerator]);

        $result = $registry->generate('field', 'unknown', []);
        expect($result)->toBe([]);
    });

    it('returns rule array when generator supports the type', function () {
        $mockGenerator = mock(ValidatorGenerator::class);
        $mockGenerator->shouldReceive('supports')->with('enum')->andReturn(true);
        $mockGenerator->shouldReceive('generate')
            ->with('status', ['values' => ['active', 'inactive']])
            ->andReturn(['status' => ['required', 'in:active,inactive']]);

        $registry = new ValidatorGeneratorRegistry([$mockGenerator]);

        $result = $registry->generate('status', 'enum', ['values' => ['active', 'inactive']]);
        expect($result)->toBe([
            'status' => ['required', 'in:active,inactive'],
        ]);
    });

    it('uses the first matching generator when multiple support the type', function () {
        $first = mock(ValidatorGenerator::class);
        $first->shouldReceive('supports')->with('string')->andReturn(true);
        $first->shouldReceive('generate')->with('name', ['max' => 255])
            ->andReturn(['name' => ['string', 'max:255']]);

        $second = mock(ValidatorGenerator::class);
        $second->shouldReceive('supports')->with('string')->andReturn(true);
        // second->generate() should not be called

        $registry = new ValidatorGeneratorRegistry([$first, $second]);

        $result = $registry->generate('name', 'string', ['max' => 255]);

        expect($result)->toBe([
            'name' => ['string', 'max:255'],
        ]);
    });
});
