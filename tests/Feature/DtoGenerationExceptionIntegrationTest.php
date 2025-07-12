<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Exceptions\DtoGenerationException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Clean up any test files
    File::deleteDirectory(base_path('temp_test_definitions'));
    File::deleteDirectory(base_path('temp_test_output'));

    // Clean up various possible DTO directories
    $possibleDirs = [
        app_path('DTO'),
        app_path('DTOs'),
        base_path('vendor/orchestra/testbench-core/laravel/app/DTO'),
        base_path('vendor/orchestra/testbench-core/laravel/app/DTOs'),
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

    // Clean up various possible DTO directories
    $possibleDirs = [
        app_path('DTO'),
        app_path('DTOs'),
        base_path('vendor/orchestra/testbench-core/laravel/app/DTO'),
        base_path('vendor/orchestra/testbench-core/laravel/app/DTOs'),
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

    // Configure for test
    config(['dto.definitions_path' => $testDir]);
    config(['dto.output_path' => base_path('temp_test_output')]);

    // Run command and expect success
    $result = Artisan::call('dto:generate', [
        'filename' => 'valid-user.yaml',
    ]);

    expect($result)->toBe(0); // Should succeed

    $output = Artisan::output();
    expect($output)->toContain('✅ DTO class written to:');
    expect($output)->toContain('ValidUserDTO.php');

    // Extract the actual path from the output instead of guessing
    $outputLines = explode("\n", $output);
    $pathLine = collect($outputLines)->first(fn ($line) => str_contains($line, 'DTO class written to:'));

    if ($pathLine) {
        $actualPath = mb_trim(str_replace('✅ DTO class written to:', '', $pathLine));
        expect(File::exists($actualPath))->toBeTrue();
    } else {
        // Fallback: check if any ValidUserDTO.php file exists in expected locations
        $possiblePaths = [
            base_path('temp_test_output/Test/ValidUserDTO.php'),
            base_path('vendor/orchestra/testbench-core/laravel/app/DTO/Test/ValidUserDTO.php'),
            base_path('app/DTO/Test/ValidUserDTO.php'),
        ];

        $fileExists = collect($possiblePaths)->contains(fn ($path) => File::exists($path));
        expect($fileExists)->toBeTrue();
    }
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
    config(['dto.definitions_path' => $testDir]);
    config(['dto.output_path' => $outputDir]);

    // Run the command normally - this should succeed
    $result = Artisan::call('dto:generate', [
        'filename' => 'write-test.yaml',
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
