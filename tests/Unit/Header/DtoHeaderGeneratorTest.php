<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Headers\DtoHeaderGenerator;

describe('DtoHeaderGenerator', function () {
    it('generates class name from dto header', function () {
        $generator = new DtoHeaderGenerator();

        $yaml = ['dto' => 'MyCustomDTO'];
        $result = $generator->generate('dto', $yaml, new DtoGenerationContext());

        expect($result)->toBe('MyCustomDTO');
    });

    it('falls back to DTO name if dto header is missing', function () {
        $generator = new DtoHeaderGenerator();

        $yaml = [];
        $result = $generator->generate('dto', $yaml, new DtoGenerationContext());

        expect($result)->toBe('UnnamedDto');
    });
});
