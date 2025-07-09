<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\DtoPaths;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    // Reset config before each test
    Config::set('dto.definitions_path', null);
    Config::set('dto.output_path', null);
    Config::set('dto.namespace', null);
});

it('returns default definition directory when config is not set', function () {
    Config::set('dto.definitions_path', base_path('database/dto_definitions'));

    $path = DtoPaths::definitionDir();

    expect($path)->toEndWith('database/dto_definitions');
});

it('returns configured definition directory', function () {
    Config::set('dto.definitions_path', base_path('custom/definitions'));

    $path = DtoPaths::definitionDir();

    expect($path)->toBe(base_path('custom/definitions'));
});

it('returns default output directory when config is not set', function () {
    Config::set('dto.output_path', base_path('app/DTOs'));

    $path = DtoPaths::dtoOutputDir();

    expect($path)->toEndWith('app/DTOs');
});

it('returns configured output directory', function () {
    Config::set('dto.output_path', base_path('custom/output'));

    $path = DtoPaths::dtoOutputDir();

    expect($path)->toBe(base_path('custom/output'));
});

it('returns dto file path', function () {
    Config::set('dto.output_path', base_path('app/DTOs'));

    $path = DtoPaths::dtoFilePath('UserDTO');

    expect($path)->toBe(base_path('app/DTOs/UserDTO.php'));
});

it('returns definition file path', function () {
    Config::set('dto.definitions_path', base_path('database/dto_definitions'));

    $path = DtoPaths::definitionFilePath('UserDTO');

    expect($path)->toBe(base_path('database/dto_definitions/user.yaml'));
});

it('returns manual namespace when configured', function () {
    Config::set('dto.namespace', 'Custom\\Namespace\\DTOs');

    $namespace = DtoPaths::dtoNamespace();

    expect($namespace)->toBe('Custom\\Namespace\\DTOs');
});

it('trims leading and trailing backslashes from manual namespace', function () {
    Config::set('dto.namespace', '\\Custom\\Namespace\\DTOs\\');

    $namespace = DtoPaths::dtoNamespace();

    expect($namespace)->toBe('Custom\\Namespace\\DTOs');
});

it('derives namespace from output path when manual namespace is not set', function () {
    Config::set('dto.output_path', base_path('app/DTOs'));
    Config::set('dto.namespace', null);

    $namespace = DtoPaths::dtoNamespace();

    expect($namespace)->toBe('App\\DTOs');
});
