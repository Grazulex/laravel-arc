<?php

use Grazulex\Arc\Commands\MakeDtoCommand;
use Illuminate\Support\Facades\File;

describe('MakeDtoCommand', function () {
    beforeEach(function () {
        // Clean up any test files
        $testPaths = ['app/Data', 'app/Datas', 'app/Custom'];
        foreach ($testPaths as $path) {
            $fullPath = base_path($path);
            if (File::exists($fullPath)) {
                File::deleteDirectory($fullPath);
            }
        }
    });

    afterEach(function () {
        // Clean up test files
        $testPaths = ['app/Data', 'app/Datas', 'app/Custom'];
        foreach ($testPaths as $path) {
            $fullPath = base_path($path);
            if (File::exists($fullPath)) {
                File::deleteDirectory($fullPath);
            }
        }
    });

    it('creates a basic DTO without model', function () {
        $this->artisan('make:dto', ['name' => 'TestUser'])
            ->expectsOutput('DTO TestUserDTO.php created successfully in app/Data!')
            ->assertExitCode(0);

        $filePath = base_path('app/Data/TestUserDTO.php');
        expect(File::exists($filePath))->toBeTrue();

        $content = File::get($filePath);
        expect($content)->toContain('namespace App\\Data;')
            ->and($content)->toContain('class TestUserDTO extends LaravelArcDTO')
            ->and($content)->toContain('// Add your properties here');
    });

    it('creates a DTO with custom path', function () {
        $this->artisan('make:dto', [
            'name' => 'CustomUser',
            '--path' => 'app/Datas'
        ])
            ->expectsOutput('DTO CustomUserDTO.php created successfully in app/Datas!')
            ->assertExitCode(0);

        $filePath = base_path('app/Datas/CustomUserDTO.php');
        expect(File::exists($filePath))->toBeTrue();

        $content = File::get($filePath);
        expect($content)->toContain('namespace App\\Datas;');
    });

    it('automatically adds DTO suffix if not present', function () {
        $this->artisan('make:dto', ['name' => 'User'])
            ->expectsOutput('DTO UserDTO.php created successfully in app/Data!')
            ->assertExitCode(0);

        $filePath = base_path('app/Data/UserDTO.php');
        expect(File::exists($filePath))->toBeTrue();

        $content = File::get($filePath);
        expect($content)->toContain('class UserDTO extends LaravelArcDTO');
    });

    it('does not overwrite existing DTO', function () {
        // Create first DTO
        $this->artisan('make:dto', ['name' => 'ExistingUser'])
            ->assertExitCode(0);

        // Try to create same DTO again
        $this->artisan('make:dto', ['name' => 'ExistingUser'])
            ->expectsOutput('DTO ExistingUserDTO.php already exists!')
            ->assertExitCode(1);
    });

    it('creates directory if it does not exist', function () {
        $customPath = 'app/Custom/DTOs';
        expect(File::exists(base_path($customPath)))->toBeFalse();

        $this->artisan('make:dto', [
            'name' => 'NewUser',
            '--path' => $customPath
        ])
            ->assertExitCode(0);

        expect(File::exists(base_path($customPath)))->toBeTrue();
        expect(File::exists(base_path($customPath . '/NewUserDTO.php')))->toBeTrue();
    });
});

