<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\DtoPathResolver;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

describe('DtoPathResolver Integration', function () {
    beforeEach(function () {
        // Reset config before each test
        Config::set('dto.definitions_path', null);
        Config::set('dto.output_path', null);
        Config::set('dto.namespace', null);
    });

    it('can resolve paths for nested namespaces', function () {
        // Setup
        $definitionPath = __DIR__.'/temp_definitions';
        $outputPath = __DIR__.'/temp_output';

        File::ensureDirectoryExists($definitionPath);
        Config::set('dto.definitions_path', $definitionPath);
        Config::set('dto.output_path', $outputPath);
        Config::set('dto.namespace', 'App\\DTO');

        // Create a test YAML with nested namespace
        $yamlContent = <<<YAML
header:
  dto: AdminUserDTO
  table: admin_users
  model: App\Models\AdminUser
  namespace: App\DTO\Admin

fields:
  id:
    type: integer
  name:
    type: string
  email:
    type: string
    required: false

options:
  timestamps: false
  soft_deletes: false
  namespace: App\DTO\Admin
YAML;

        File::put($definitionPath.'/admin-user.yaml', $yamlContent);

        // Test path resolution
        $expectedPath = DtoPathResolver::resolveOutputPath('AdminUserDTO', 'App\\DTO\\Admin');
        expect($expectedPath)->toBe($outputPath.'/Admin/AdminUserDTO.php');

        // Generate the DTO
        $exitCode = Artisan::call('dto:generate', [
            'filename' => 'admin-user.yaml',
            '--force' => true,
        ]);

        expect($exitCode)->toBe(0);
        expect(File::exists($expectedPath))->toBeTrue();

        $content = File::get($expectedPath);
        expect($content)->toContain('namespace App\\DTO\\Admin');
        expect($content)->toContain('class AdminUserDTO');

        // Cleanup
        File::deleteDirectory($definitionPath);
        File::deleteDirectory($outputPath);
    });

    it('can resolve paths for completely different namespaces', function () {
        // Setup
        $definitionPath = __DIR__.'/temp_definitions';
        $outputPath = __DIR__.'/temp_output';

        File::ensureDirectoryExists($definitionPath);
        Config::set('dto.definitions_path', $definitionPath);
        Config::set('dto.output_path', $outputPath);
        Config::set('dto.namespace', 'App\\DTO');

        // Create a test YAML with different namespace
        $yamlContent = <<<YAML
header:
  dto: CustomDTO
  table: custom_data
  model: App\Models\Custom
  namespace: MyCompany\DataObjects

fields:
  id:
    type: integer
  name:
    type: string

options:
  timestamps: false
  soft_deletes: false
  namespace: MyCompany\DataObjects
YAML;

        File::put($definitionPath.'/custom.yaml', $yamlContent);

        // Test path resolution - should go to base_path due to different namespace
        $expectedPath = DtoPathResolver::resolveOutputPath('CustomDTO', 'MyCompany\\DataObjects');
        expect($expectedPath)->toBe(base_path('MyCompany/DataObjects/CustomDTO.php'));

        // Generate the DTO
        $exitCode = Artisan::call('dto:generate', [
            'filename' => 'custom.yaml',
            '--force' => true,
        ]);

        expect($exitCode)->toBe(0);
        expect(File::exists($expectedPath))->toBeTrue();

        $content = File::get($expectedPath);
        expect($content)->toContain('namespace MyCompany\\DataObjects');
        expect($content)->toContain('class CustomDTO');

        // Cleanup
        File::deleteDirectory($definitionPath);
        File::deleteDirectory(base_path('MyCompany'));
    });

    it('can derive namespace from custom output paths', function () {
        // Test various output paths and their derived namespaces
        $testCases = [
            'app/Data/DTOs' => 'App\\Data\\DTOs',
            'app/Custom/Objects' => 'App\\Custom\\Objects',
            'src/Domain/DTOs' => 'Src\\Domain\\DTOs',
        ];

        foreach ($testCases as $path => $expectedNamespace) {
            Config::set('dto.output_path', base_path($path));
            Config::set('dto.namespace', null); // Let it derive

            $derivedNamespace = Grazulex\LaravelArc\Support\DtoPaths::dtoNamespace();
            expect($derivedNamespace)->toBe($expectedNamespace, "Failed for path: $path");
        }
    });
});
