<?php

declare(strict_types=1);

namespace Tests\Feature\DtoGenerator;

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\DtoGenerator;
use Grazulex\LaravelArc\Generator\Fields\DtoFieldGenerator;

it('can generate simple dto field', function () {
    $generator = new DtoFieldGenerator();
    $context = new DtoGenerationContext();

    $definition = [
        'type' => 'dto',
        'dto' => 'UserDTO',
        'required' => true,
    ];

    $result = $generator->generate('author', $definition, $context);

    expect($result)->toBe('public readonly \\UserDTO $author,');
});

it('can generate dto field with YAML definition', function () {
    $yaml = [
        'header' => [
            'dto' => 'TestDTO',
            'model' => 'App\\Models\\Test',
            'table' => 'tests',
        ],
        'fields' => [
            'id' => [
                'type' => 'uuid',
                'required' => true,
            ],
            'author' => [
                'type' => 'dto',
                'dto' => 'UserDTO',
                'required' => true,
            ],
        ],
        'options' => [
            'namespace' => 'App\\DTO',
        ],
    ];

    $generator = DtoGenerator::make();
    $code = $generator->generateFromDefinition($yaml);

    expect($code)->toContain('public readonly \\UserDTO $author,');
});
