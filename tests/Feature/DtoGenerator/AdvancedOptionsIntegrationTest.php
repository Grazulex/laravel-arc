<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerator;

describe('Advanced Options Integration', function () {
    it('generates DTO with UUID option', function () {
        $yaml = [
            'header' => ['dto' => 'UuidTestDTO'],
            'fields' => [
                'name' => ['type' => 'string', 'required' => true],
            ],
            'options' => [
                'uuid' => true,
            ],
        ];

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($yaml);

        expect($result)
            ->toContain('final class UuidTestDTO')
            ->toContain('public readonly string $id,')
            ->toContain('generateUuid()')
            ->toContain("'id' => ['uuid', 'required']");
    });

    it('generates DTO with versioning option', function () {
        $yaml = [
            'header' => ['dto' => 'VersionTestDTO'],
            'fields' => [
                'name' => ['type' => 'string', 'required' => true],
            ],
            'options' => [
                'versioning' => true,
            ],
        ];

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($yaml);

        expect($result)
            ->toContain('final class VersionTestDTO')
            ->toContain('public readonly int $version = 1,')
            ->toContain('nextVersion()')
            ->toContain("'version' => ['integer', 'required', 'min:1']");
    });

    it('generates DTO with taggable option', function () {
        $yaml = [
            'header' => ['dto' => 'TagTestDTO'],
            'fields' => [
                'name' => ['type' => 'string', 'required' => true],
            ],
            'options' => [
                'taggable' => true,
            ],
        ];

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($yaml);

        expect($result)
            ->toContain('final class TagTestDTO')
            ->toContain('public readonly ?array $tags')
            ->toContain('addTag(')
            ->toContain('hasTag(')
            ->toContain("'tags' => ['array', 'nullable']");
    });

    it('generates DTO with multiple options combined', function () {
        $yaml = [
            'header' => ['dto' => 'CombinedTestDTO'],
            'fields' => [
                'name' => ['type' => 'string', 'required' => true],
            ],
            'options' => [
                'timestamps' => true,
                'uuid' => true,
                'versioning' => true,
            ],
        ];

        $generator = DtoGenerator::make();
        $result = $generator->generateFromDefinition($yaml);

        expect($result)
            ->toContain('final class CombinedTestDTO')
            ->toContain('public readonly string $id,') // UUID
            ->toContain('public readonly \Carbon\Carbon $created_at,') // Timestamps
            ->toContain('public readonly int $version = 1,') // Versioning
            ->toContain('generateUuid()')
            ->toContain('nextVersion()');
    });
});
