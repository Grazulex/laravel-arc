<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerator;
use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;

describe('Advanced Options Integration', function () {
    beforeEach(function () {
        // Ensure traits are registered
        BehavioralTraitRegistry::registerDefaults();
    });

    it('generates DTO with UUID option', function () {
        $yaml = [
            'header' => [
                'dto' => 'UuidTestDTO',
                'traits' => ['HasUuid'],
            ],
            'fields' => [
                'name' => ['type' => 'string', 'required' => true],
            ],
        ];

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($yaml);

        expect($result)
            ->toContain('final class UuidTestDTO')
            ->toContain('public readonly string $id,') // Field added by trait
            ->toContain('use HasUuid;') // Trait is used
            ->toContain("'id' => ['uuid', 'required']"); // Validation rules from trait
    });

    it('generates DTO with versioning option', function () {
        $yaml = [
            'header' => [
                'dto' => 'VersionTestDTO',
                'traits' => ['HasVersioning'],
            ],
            'fields' => [
                'name' => ['type' => 'string', 'required' => true],
            ],
        ];

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($yaml);

        expect($result)
            ->toContain('final class VersionTestDTO')
            ->toContain('public readonly int $version = 1,') // Field added by trait
            ->toContain('use HasVersioning;') // Trait is used
            ->toContain("'version' => ['integer', 'min:1']"); // Validation rules from trait
    });

    it('generates DTO with taggable option', function () {
        $yaml = [
            'header' => [
                'dto' => 'TagTestDTO',
                'traits' => ['HasTagging'],
            ],
            'fields' => [
                'name' => ['type' => 'string', 'required' => true],
            ],
        ];

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($yaml);

        expect($result)
            ->toContain('final class TagTestDTO')
            ->toContain('public readonly ?array $tags') // Field added by trait
            ->toContain('use HasTagging;') // Trait is used
            ->toContain("'tags' => ['array']"); // Validation rules from trait
    });

    it('generates DTO with multiple options combined', function () {
        $yaml = [
            'header' => [
                'dto' => 'CombinedTestDTO',
                'traits' => ['HasUuid', 'HasTimestamps', 'HasVersioning'],
            ],
            'fields' => [
                'name' => ['type' => 'string', 'required' => true],
            ],
        ];

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($yaml);

        expect($result)
            ->toContain('final class CombinedTestDTO')
            ->toContain('public readonly string $id,') // UUID field
            ->toContain('public readonly ?\Carbon\Carbon $created_at = null,') // Timestamp fields (nullable)
            ->toContain('public readonly int $version = 1,') // Versioning field
            ->toContain('use HasUuid;') // UUID trait
            ->toContain('use HasTimestamps;') // Timestamps trait
            ->toContain('use HasVersioning;'); // Versioning trait
    });
});
