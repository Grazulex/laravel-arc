<?php

declare(strict_types=1);

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

        $yaml = ['dto' => 'SampleDTO', 'table' => 'SampleTable', 'model' => 'Models\\SampleModel'];
        $result = $registry->generateAll($yaml, 'FallbackDTO');

        expect($result)->toHaveKey('dto');
        expect($result)->toHaveKey('table');
        expect($result)->toHaveKey('model');
        expect($result['dto'])->toContain('final readonly class SampleDTO');
        expect($result['table'])->toContain("/**\n * Data Transfer Object for table `SampleTable`.\n */");
        expect($result['model'])->toContain('use Models\SampleModel;');
    });
});
