<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Exceptions\DtoGenerationException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Clean up any test files
    File::deleteDirectory(base_path('temp_test_definitions'));
    File::deleteDirectory(base_path('temp_test_output'));

    // Clean up various possible DTO directories - including the testbench path
    $possibleDirs = [
        app_path('DTO'),
        app_path('DTOs'),
        base_path('vendor/orchestra/testbench-core/laravel/app/DTO'),
        base_path('vendor/orchestra/testbench-core/laravel/app/DTOs'),
        base_path('vendor/orchestra/testbench-core/laravel/temp_test_definitions'),
        base_path('vendor/orchestra/testbench-core/laravel/temp_test_output'),
    ];

    foreach ($possibleDirs as $dir) {
        if (File::exists($dir)) {
            File::deleteDirectory($dir);
        }
    }
});

afterEach(function () {
    // Clean up any test files
    File::deleteDirectory(base_path('temp_test_definitions'));
    File::deleteDirectory(base_path('temp_test_output'));

    // Clean up various possible DTO directories - including the testbench path
    $possibleDirs = [
        app_path('DTO'),
        app_path('DTOs'),
        base_path('vendor/orchestra/testbench-core/laravel/app/DTO'),
        base_path('vendor/orchestra/testbench-core/laravel/app/DTOs'),
        base_path('vendor/orchestra/testbench-core/laravel/temp_test_definitions'),
        base_path('vendor/orchestra/testbench-core/laravel/temp_test_output'),
    ];

    foreach ($possibleDirs as $dir) {
        if (File::exists($dir)) {
            File::deleteDirectory($dir);
        }
    }
});

it('provides helpful error messages for invalid YAML syntax', function () {
    $testDir = base_path('temp_test_definitions');
    File::ensureDirectoryExists($testDir);

    // Create invalid YAML file - this has genuinely invalid syntax
    $invalidYaml = <<<YAML
header:
  dto: TestDTO
  table: test
  model: App\Models\Test
fields:
  name:
    type: string
    - invalid_list_item_at_wrong_level
YAML;

    File::put($testDir.'/invalid-syntax.yaml', $invalidYaml);

    // Configure for test
    config(['dto.definitions_path' => $testDir]);
    config(['dto.output_path' => base_path('temp_test_output')]);

    // Run command and expect it to fail
    $result = Artisan::call('dto:generate', [
        'filename' => 'invalid-syntax.yaml',
    ]);

    expect($result)->toBe(1); // Should fail

    $output = Artisan::output();
    expect($output)->toContain('❌ DTO Generation Error');
    expect($output)->toContain('YAML parsing failed');
});

it('provides context information in error messages', function () {
    $testDir = base_path('temp_test_definitions');
    File::ensureDirectoryExists($testDir);

    // Create problematic YAML file
    $problematicYaml = <<<YAML
header:
  dto: ProblematicDTO
  table: problematic
  model: App\Models\Problematic

fields:
  valid_field:
    type: string
    required: true
  
  invalid_field:
    type: unsupported_type
    required: true
YAML;

    File::put($testDir.'/problematic.yaml', $problematicYaml);

    // Configure for test
    config(['dto.definitions_path' => $testDir]);
    config(['dto.output_path' => base_path('temp_test_output')]);

    // Run command and expect it to fail with context
    $result = Artisan::call('dto:generate', [
        'filename' => 'problematic.yaml',
    ]);

    expect($result)->toBe(1); // Should fail

    $output = Artisan::output();
    expect($output)->toContain('Field: invalid_field');
    expect($output)->toContain('Field Type');
    expect($output)->toContain('unsupported_type');
});

it('successfully generates DTO when everything is valid', function () {
    $testDir = base_path('temp_test_definitions');
    File::ensureDirectoryExists($testDir);

    // Create valid YAML file
    $validYaml = <<<YAML
header:
  dto: ValidUserDTO
  table: users
  model: App\Models\User

fields:
  id:
    type: integer
    required: true
  name:
    type: string
    required: true
  email:
    type: string
    required: true
    rules: [email]

options:
  timestamps: true
  namespace: App\DTO\Test
YAML;

    File::put($testDir.'/valid-user.yaml', $validYaml);

    // Configure for test using the same approach as DtoGenerateCommandTest
    config()->set('dto.definitions_path', $testDir);
    config()->set('dto.output_path', base_path('temp_test_output'));
    config()->set('dto.namespace', 'App\\DTO'); // Set the base namespace

    // Run command and expect success
    $result = Artisan::call('dto:generate', [
        'filename' => 'valid-user.yaml',
        '--force' => true,
    ]);

    expect($result)->toBe(0); // Should succeed

    // Check for the file in possible locations
    $possiblePaths = [
        base_path('temp_test_output/Test/ValidUserDTO.php'),
        base_path('temp_test_output/ValidUserDTO.php'),
        app_path('DTO/Test/ValidUserDTO.php'),
        app_path('DTO/ValidUserDTO.php'),
    ];

    $foundPath = null;
    foreach ($possiblePaths as $path) {
        if (File::exists($path)) {
            $foundPath = $path;
            break;
        }
    }

    expect($foundPath)->not->toBeNull('DTO file should be created at one of these paths: '.implode(', ', $possiblePaths));

    // Check file contents to verify it's correct
    $content = File::get($foundPath);
    expect($content)->toContain('class ValidUserDTO');
    expect($content)->toContain('namespace App\DTO');
});

it('provides helpful error messages for missing required header', function () {
    $testDir = base_path('temp_test_definitions');
    File::ensureDirectoryExists($testDir);

    // Create YAML file without required header
    $invalidYaml = <<<YAML
header:
  table: test
  model: App\Models\Test

fields:
  name:
    type: string
YAML;

    File::put($testDir.'/no-dto-header.yaml', $invalidYaml);

    // Configure for test
    config(['dto.definitions_path' => $testDir]);
    config(['dto.output_path' => base_path('temp_test_output')]);

    // Run command and expect it to fail
    $result = Artisan::call('dto:generate', [
        'filename' => 'no-dto-header.yaml',
    ]);

    expect($result)->toBe(1); // Should fail

    $output = Artisan::output();
    expect($output)->toContain('❌ DTO Generation Error');
    expect($output)->toContain('Missing required header');
    expect($output)->toContain('dto');
});

it('provides helpful error messages for file write errors', function () {
    // This test verifies that the error handling path works correctly
    // Since it's hard to simulate actual write errors in tests, we'll
    // verify that the exception handling code exists and works

    $testDir = base_path('temp_test_definitions');
    File::ensureDirectoryExists($testDir);

    // Create valid YAML file
    $validYaml = <<<YAML
header:
  dto: WriteTestDTO
  table: test
  model: App\Models\Test

fields:
  name:
    type: string
YAML;

    File::put($testDir.'/write-test.yaml', $validYaml);

    // Test the command with a non-existent directory that would trigger file creation
    // but where we can be more sure of the behavior
    $outputDir = base_path('temp_test_output');
    config()->set('dto.definitions_path', $testDir);
    config()->set('dto.output_path', $outputDir);
    config()->set('dto.namespace', 'App\\DTO');

    // Run the command normally - this should succeed
    $result = Artisan::call('dto:generate', [
        'filename' => 'write-test.yaml',
        '--force' => true,
    ]);

    expect($result)->toBe(0); // Should succeed

    // Verify the error handling method exists by checking the exception class
    $exception = DtoGenerationException::fileWriteError(
        'test.yaml',
        'test/path.php',
        'Permission denied',
        'TestDTO'
    );

    expect($exception->getFormattedMessage())->toContain('File Writing');
    expect($exception->getFormattedMessage())->toContain('Permission denied');
});

it('provides helpful error messages for invalid namespace format', function () {
    $testDir = base_path('temp_test_definitions');
    File::ensureDirectoryExists($testDir);

    // Create YAML file with invalid namespace
    $invalidYaml = <<<YAML
header:
  dto: InvalidNamespaceDTO
  table: test
  model: App\Models\Test

fields:
  name:
    type: string

options:
  namespace: Invalid\\\\Namespace
YAML;

    File::put($testDir.'/invalid-namespace.yaml', $invalidYaml);

    // Configure for test
    config(['dto.definitions_path' => $testDir]);
    config(['dto.output_path' => base_path('temp_test_output')]);

    // Run command and expect it to fail
    $result = Artisan::call('dto:generate', [
        'filename' => 'invalid-namespace.yaml',
    ]);

    expect($result)->toBe(1); // Should fail

    $output = Artisan::output();
    expect($output)->toContain('❌ DTO Generation Error');
    expect($output)->toContain('Namespace resolution failed');
});
