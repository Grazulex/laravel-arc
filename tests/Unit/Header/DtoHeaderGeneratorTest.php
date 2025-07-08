<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Headers\DtoHeaderGenerator;

it('generates class name from dto header', function () {
    $generator = new DtoHeaderGenerator();

    $yaml = ['dto' => 'MyCustomDTO'];
    $result = $generator->generate($yaml, 'FallbackDTO');

    expect($result)->toContain('final readonly class MyCustomDTO');
});

it('falls back to DTO name if dto header is missing', function () {
    $generator = new DtoHeaderGenerator();

    $yaml = [];
    $result = $generator->generate($yaml, 'FallbackDTO');

    expect($result)->toContain('final readonly class FallbackDTO');
});
