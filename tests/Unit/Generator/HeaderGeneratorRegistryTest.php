<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Contracts\HeaderGenerator;
use Grazulex\LaravelArc\Generator\HeaderGeneratorRegistry;

describe('HeaderGeneratorRegistry', function () {
    it('throws exception when invalid generator is provided', function () {
        expect(fn () => new HeaderGeneratorRegistry(['invalid']))
            ->toThrow(InvalidArgumentException::class, 'Each generator must implement HeaderGenerator.');
    });

    it('accepts valid generators', function () {
        $mockGenerator = mock(HeaderGenerator::class);
        $registry = new HeaderGeneratorRegistry([$mockGenerator]);

        expect($registry)->toBeInstanceOf(HeaderGeneratorRegistry::class);
    });
});
