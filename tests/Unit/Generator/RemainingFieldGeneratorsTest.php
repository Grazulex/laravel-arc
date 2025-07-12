<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Fields\DateFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\DateTimeFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\DecimalFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\IdFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\JsonFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\TextFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\TimeFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\UuidFieldGenerator;

// Dataset avec les générateurs de champs restants
dataset('remaining_field_generators', function () {
    return [
        'date' => [
            DateFieldGenerator::class,
            ['date'],
            ['string', 'integer'],
            [
                'simple' => [
                    'field' => 'birth_date',
                    'definition' => ['type' => 'date'],
                    'expected' => 'public \Carbon\Carbon $birth_date;',
                ],
            ],
        ],
        'datetime' => [
            DateTimeFieldGenerator::class,
            ['datetime'],
            ['string', 'integer'],
            [
                'simple' => [
                    'field' => 'created_at',
                    'definition' => ['type' => 'datetime'],
                    'expected' => 'public \Carbon\Carbon $created_at;',
                ],
            ],
        ],
        'time' => [
            TimeFieldGenerator::class,
            ['time'],
            ['string', 'integer'],
            [
                'simple' => [
                    'field' => 'start_time',
                    'definition' => ['type' => 'time'],
                    'expected' => 'public \Carbon\Carbon $start_time;',
                ],
            ],
        ],
        'decimal' => [
            DecimalFieldGenerator::class,
            ['decimal'],
            ['string', 'integer'],
            [
                'simple' => [
                    'field' => 'price',
                    'definition' => ['type' => 'decimal'],
                    'expected' => 'public string $price;',
                ],
            ],
        ],
        'json' => [
            JsonFieldGenerator::class,
            ['json'],
            ['string', 'integer'],
            [
                'simple' => [
                    'field' => 'metadata',
                    'definition' => ['type' => 'json'],
                    'expected' => 'public array $metadata;',
                ],
            ],
        ],
        'text' => [
            TextFieldGenerator::class,
            ['text'],
            ['string', 'integer'],
            [
                'simple' => [
                    'field' => 'description',
                    'definition' => ['type' => 'text'],
                    'expected' => 'public string $description;',
                ],
            ],
        ],
        'uuid' => [
            UuidFieldGenerator::class,
            ['uuid'],
            ['string', 'integer'],
            [
                'simple' => [
                    'field' => 'id',
                    'definition' => ['type' => 'uuid'],
                    'expected' => 'public string $id;',
                ],
            ],
        ],
        'id' => [
            IdFieldGenerator::class,
            ['id'],
            ['string', 'boolean'],
            [
                'simple' => [
                    'field' => 'id',
                    'definition' => ['type' => 'id'],
                    'expected' => 'public string $id;',
                ],
            ],
        ],
    ];
});

describe('Remaining Field Generators', function () {
    it('supports correct types', function (string $generatorClass, array $supportedTypes, array $unsupportedTypes) {
        $generator = new $generatorClass();

        // Test supported types
        foreach ($supportedTypes as $type) {
            expect($generator->supports($type))->toBe(true, "Should support type: $type");
        }

        // Test unsupported types
        foreach ($unsupportedTypes as $type) {
            expect($generator->supports($type))->toBe(false, "Should not support type: $type");
        }
    })->with('remaining_field_generators');

    it('generates correct field code', function (string $generatorClass, array $supportedTypes, array $unsupportedTypes, array $testCases) {
        $generator = new $generatorClass();
        $context = new DtoGenerationContext();

        foreach ($testCases as $caseName => $case) {
            $result = $generator->generate($case['field'], $case['definition'], $context);
            expect($result)->toBe($case['expected'], "Failed for case: $caseName");
        }
    })->with('remaining_field_generators');
});
