<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Contracts\FieldGenerator;
use Grazulex\LaravelArc\Generator\FieldGeneratorRegistry;

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
});
