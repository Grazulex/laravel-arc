<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\HeaderGeneratorRegistry;
use Grazulex\LaravelArc\Generator\Headers\DtoHeaderGenerator;
use Grazulex\LaravelArc\Generator\Headers\ModelHeaderGenerator;
use Grazulex\LaravelArc\Generator\Headers\TableHeaderGenerator;

describe('HeaderGeneratorRegistry', function () {
    it('calls only supported generators for the headers', function () {
        $registry = new HeaderGeneratorRegistry([
            new DtoHeaderGenerator(),
            new ModelHeaderGenerator(),
            new TableHeaderGenerator(),
        ]);

        $context = new DtoGenerationContext();

        $yaml = [
            'header' => [
                'dto' => 'SampleDTO',
                'table' => 'SampleTable',
                'model' => 'Models\\SampleModel',
            ],
        ];

        $result = $registry->generateAll($yaml, $context);

        expect($result)->toHaveKey('dto');
        expect($result)->toHaveKey('table');
        expect($result)->toHaveKey('model');

        expect($result['dto'])->toBe('SampleDTO');
        expect($result['table'])->toBe('SampleTable');
        expect($result['model'])->toBe('\\Models\\SampleModel');
    });

    it('throws exception when invalid generator is provided', function () {
        expect(fn () => new HeaderGeneratorRegistry(['invalid']))
            ->toThrow(InvalidArgumentException::class, 'Each generator must implement HeaderGenerator.');
    });

    it('accepts valid generators', function () {
        $mockGenerator = mock(Grazulex\LaravelArc\Contracts\HeaderGenerator::class);
        $registry = new HeaderGeneratorRegistry([$mockGenerator]);

        expect($registry)->toBeInstanceOf(HeaderGeneratorRegistry::class);
    });
});
