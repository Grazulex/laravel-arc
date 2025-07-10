<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\ValidatorGeneratorRegistry;

describe('ValidatorGeneratorRegistry', function () {
    it('throws exception when invalid generator is provided', function () {
        $context = new DtoGenerationContext();
        expect(fn () => new ValidatorGeneratorRegistry(['invalid'], $context))
            ->toThrow(InvalidArgumentException::class, 'Each generator must implement ValidatorGenerator.');
    });

    it('accepts valid generators', function () {
        $mockGenerator = mock(ValidatorGenerator::class);
        $context = new DtoGenerationContext();
        $registry = new ValidatorGeneratorRegistry([$mockGenerator], $context);

        expect($registry)->toBeInstanceOf(ValidatorGeneratorRegistry::class);
    });

    it('returns empty array when no generator supports the type', function () {
        $mockGenerator = mock(ValidatorGenerator::class);
        $mockGenerator->shouldReceive('supports')->with('unknown')->andReturn(false);

        $context = new DtoGenerationContext();
        $registry = new ValidatorGeneratorRegistry([$mockGenerator], $context);

        $result = $registry->generate('field', ['type' => 'unknown'], $context);
        expect($result)->toBe([]);
    });

    it('returns rule array when generator supports the type', function () {
        $mockGenerator = mock(ValidatorGenerator::class);
        $mockGenerator->shouldReceive('supports')->with('enum')->andReturn(true);
        $mockGenerator->shouldReceive('generate')
            ->with('status', ['type' => 'enum', 'values' => ['active', 'inactive']], Mockery::type(DtoGenerationContext::class))
            ->andReturn(['status' => ['required', 'in:active,inactive']]);

        $context = new DtoGenerationContext();
        $registry = new ValidatorGeneratorRegistry([$mockGenerator], $context);

        $result = $registry->generate('status', ['type' => 'enum', 'values' => ['active', 'inactive']], $context);
        expect($result)->toBe([
            'status' => ['required', 'in:active,inactive'],
        ]);
    });

    it('uses the first matching generator when multiple support the type', function () {
        $first = mock(ValidatorGenerator::class);
        $first->shouldReceive('supports')->with('string')->andReturn(true);
        $first->shouldReceive('generate')->with('name', ['type' => 'string', 'max' => 255], Mockery::type(DtoGenerationContext::class))
            ->andReturn(['name' => ['string', 'max:255']]);

        $second = mock(ValidatorGenerator::class);
        $second->shouldReceive('supports')->with('string')->andReturn(true);

        $context = new DtoGenerationContext();
        $registry = new ValidatorGeneratorRegistry([$first, $second], $context);

        $result = $registry->generate('name', ['type' => 'string', 'max' => 255], $context);

        expect($result)->toBe([
            'name' => ['string', 'max:255'],
        ]);
    });
});
