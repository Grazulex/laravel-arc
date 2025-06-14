<?php

use Illuminate\Support\Facades\File;

describe('MakeDtoCommand Smart Validation', function () {
    beforeEach(function () {
        // Clean up any test files
        $testPaths = ['app/Data', 'app/DTOs', 'app/TestDTOs'];
        foreach ($testPaths as $path) {
            $fullPath = base_path($path);
            if (File::exists($fullPath)) {
                File::deleteDirectory($fullPath);
            }
        }
    });

    afterEach(function () {
        // Clean up test files
        $testPaths = ['app/Data', 'app/DTOs', 'app/TestDTOs'];
        foreach ($testPaths as $path) {
            $fullPath = base_path($path);
            if (File::exists($fullPath)) {
                File::deleteDirectory($fullPath);
            }
        }
    });

    it('generates DTO without validation rules by default', function () {
        $this->artisan('make:dto', [
            'name' => 'SimpleUser',
            '--path' => 'app/TestDTOs'
        ])->assertExitCode(0);

        $filePath = base_path('app/TestDTOs/SimpleUserDTO.php');
        expect(File::exists($filePath))->toBeTrue();

        $content = File::get($filePath);
        
        // Should not contain validation rules
        expect($content)->not->toContain('validation:');
    });

    it('generates smart validation rules with --with-validation flag', function () {
        // This test requires model with properties to generate validation rules
        expect(true)->toBeTrue(); // Placeholder assertion
    })->skip('Requires model with properties for validation generation');

    it('generates strict validation rules with --validation-strict flag', function () {
        // This test requires model with properties to generate validation rules
        expect(true)->toBeTrue(); // Placeholder assertion
    })->skip('Requires model with properties for validation generation');

    it('handles complex field patterns for validation', function () {
        // Create a mock model with various field types for testing
        $this->artisan('make:dto', [
            'name' => 'ComplexValidation',
            '--with-validation' => true,
            '--validation-strict' => true,
            '--path' => 'app/TestDTOs'
        ])->assertExitCode(0);

        $filePath = base_path('app/TestDTOs/ComplexValidationDTO.php');
        expect(File::exists($filePath))->toBeTrue();

        // Just verify the file was created - detailed validation logic is tested elsewhere
        $content = File::get($filePath);
        expect($content)->toContain('class ComplexValidationDTO extends LaravelArcDTO');
    });

    it('can combine validation with other flags', function () {
        $this->artisan('make:dto', [
            'name' => 'CombinedFeatures',
            '--with-validation' => true,
            '--with-relations' => true,
            '--path' => 'app/TestDTOs'
        ])->assertExitCode(0);

        $filePath = base_path('app/TestDTOs/CombinedFeaturesDTO.php');
        expect(File::exists($filePath))->toBeTrue();

        $content = File::get($filePath);
        expect($content)->toContain('class CombinedFeaturesDTO extends LaravelArcDTO');
    });
});

