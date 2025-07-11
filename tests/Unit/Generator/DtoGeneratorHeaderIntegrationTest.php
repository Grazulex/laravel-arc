<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerator;

describe('DtoGenerator Header Integration', function () {
    it('generates DTO with use statements', function () {
        $generator = DtoGenerator::make();

        $yaml = [
            'header' => [
                'dto' => 'UserDto',
                'model' => 'App\\Models\\User',
                'use' => [
                    'App\\Traits\\HasUuid',
                    'Illuminate\\Support\\Facades\\Validator',
                ],
            ],
            'fields' => [
                'id' => ['type' => 'integer', 'required' => true],
                'name' => ['type' => 'string', 'required' => true],
            ],
        ];

        $result = $generator->generateFromDefinition($yaml);

        expect($result)
            ->toContain('use App\\Traits\\HasUuid;')
            ->toContain('use Illuminate\\Support\\Facades\\Validator;')
            ->toContain('final class UserDto');
    });

    it('generates DTO with extends clause', function () {
        $generator = DtoGenerator::make();

        $yaml = [
            'header' => [
                'dto' => 'UserDto',
                'model' => 'App\\Models\\User',
                'extends' => 'BaseDto',
            ],
            'fields' => [
                'id' => ['type' => 'integer', 'required' => true],
                'name' => ['type' => 'string', 'required' => true],
            ],
        ];

        $result = $generator->generateFromDefinition($yaml);

        expect($result)
            ->toContain('final class UserDto extends BaseDto');
    });

    it('generates DTO with both use statements and extends clause', function () {
        $generator = DtoGenerator::make();

        $yaml = [
            'header' => [
                'dto' => 'UserDto',
                'model' => 'App\\Models\\User',
                'use' => [
                    'App\\Traits\\HasUuid',
                    'Illuminate\\Support\\Facades\\Validator',
                ],
                'extends' => 'BaseDto',
            ],
            'fields' => [
                'id' => ['type' => 'integer', 'required' => true],
                'name' => ['type' => 'string', 'required' => true],
            ],
        ];

        $result = $generator->generateFromDefinition($yaml);

        expect($result)
            ->toContain('use App\\Traits\\HasUuid;')
            ->toContain('use Illuminate\\Support\\Facades\\Validator;')
            ->toContain('final class UserDto extends BaseDto');
    });

    it('generates DTO without header extras when not specified', function () {
        $generator = DtoGenerator::make();

        $yaml = [
            'header' => [
                'dto' => 'UserDto',
                'model' => 'App\\Models\\User',
            ],
            'fields' => [
                'id' => ['type' => 'integer', 'required' => true],
                'name' => ['type' => 'string', 'required' => true],
            ],
        ];

        $result = $generator->generateFromDefinition($yaml);

        expect($result)
            ->toContain('final class UserDto ')
            ->not->toContain('use ')
            ->not->toContain('extends');
    });
});
