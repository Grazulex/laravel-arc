<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

beforeEach(function () {
    File::ensureDirectoryExists(base_path('tests/stubs/dto_definitions'));
    File::ensureDirectoryExists(base_path('tests/stubs/dto_output'));
    File::cleanDirectory(base_path('tests/stubs/dto_definitions'));
    File::cleanDirectory(base_path('tests/stubs/dto_output'));
});

afterAll(function () {
    File::cleanDirectory(base_path('tests/stubs/dto_definitions'));
    File::cleanDirectory(base_path('tests/stubs/dto_output'));
});

it('creates a YAML DTO definition file with dto:definition-init', function () {
    $pathOption = base_path('tests/stubs/dto_definitions');

    $result = Artisan::call('dto:definition-init', [
        'name' => 'ExampleDTO',
        '--model' => 'App\\Models\\Example',
        '--table' => 'examples',
        '--path' => $pathOption,
        '--force' => true,
    ]);

    expect($result)->toBe(0);

    $yamlPath = $pathOption.'/example.yaml';
    expect(File::exists($yamlPath))->toBeTrue();

    $yaml = Yaml::parseFile($yamlPath);

    expect($yaml['header']['dto'])->toBe('ExampleDTO')
        ->and($yaml['header']['model'])->toBe('App\\Models\\Example')
        ->and($yaml['header']['table'])->toBe('examples')
        ->and($yaml['header']['namespace'])->toBeString()
        ->and($yaml['header']['traits'])->toBeArray()
        ->and($yaml['fields'])->toBeArray();
});

it('lists the generated YAML file with dto:definition-list', function () {
    $pathOption = base_path('tests/stubs/dto_definitions');

    $yamlFile = $pathOption.'/example.yaml';
    File::put($yamlFile, <<<'YAML'
header:
  dto: ExampleDTO
  model: App\Models\Example
  table: examples
  namespace: App\DTO
  traits:
    - HasTimestamps
fields: []
YAML
    );

    $result = Artisan::call('dto:definition-list', [
        '--path' => $pathOption,
        '--compact' => true,
    ]);

    $output = Artisan::output();

    expect($result)->toBe(0);
    expect($output)->not->toBeEmpty();
    expect($output)->toContain('ExampleDTO');
});
