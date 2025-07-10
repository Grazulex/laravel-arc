<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Headers\TableHeaderGenerator;

describe('TableHeaderGenerator', function () {
    it('generates table annotation from table header', function () {
        $generator = new TableHeaderGenerator();
        $context = new DtoGenerationContext();

        $yaml = ['table' => 'trainings'];
        $result = $generator->generate('table', $yaml, $context);

        expect($result)->toBe('trainings');
    });

    it('returns default if table header is missing', function () {
        $generator = new TableHeaderGenerator();
        $context = new DtoGenerationContext();

        $yaml = [];
        $result = $generator->generate('table', $yaml, $context);

        expect($result)->toBe('undefined_table');
    });
});
