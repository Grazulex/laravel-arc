<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Console\Commands\DtoDefinitionInitCommand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

describe('DtoDefinitionInitCommand', function () {
    beforeEach(function () {
        // Clear any previous test files
        File::deleteDirectory(base_path('test-dto-definitions'));
        File::deleteDirectory(base_path('tests/stubs/dto_definitions'));
    });

    afterEach(function () {
        // Clean up test files
        File::deleteDirectory(base_path('test-dto-definitions'));
        File::deleteDirectory(base_path('tests/stubs/dto_definitions'));
    });

    it('fails when model option is missing', function () {
        $command = new DtoDefinitionInitCommand();

        // Mock the artisan call
        $this->artisan('dto:definition-init', [
            'name' => 'UserDTO',
            '--table' => 'users',
        ])->assertExitCode(Command::FAILURE);
    });

    it('fails when table option is missing', function () {
        $this->artisan('dto:definition-init', [
            'name' => 'UserDTO',
            '--model' => 'App\Models\User',
        ])->assertExitCode(Command::FAILURE);
    });

    it('fails when file exists without force option', function () {
        // Create test directory and file
        $testDir = base_path('test-dto-definitions');
        File::ensureDirectoryExists($testDir);
        File::put($testDir.'/user.yaml', 'existing content');

        $this->artisan('dto:definition-init', [
            'name' => 'UserDTO',
            '--model' => 'App\Models\User',
            '--table' => 'users',
            '--path' => $testDir,
        ])->assertExitCode(Command::FAILURE);
    });

    it('overwrites file when force option is used', function () {
        // Create test directory and file
        $testDir = base_path('test-dto-definitions');
        File::ensureDirectoryExists($testDir);
        File::put($testDir.'/user.yaml', 'existing content');

        $this->artisan('dto:definition-init', [
            'name' => 'UserDTO',
            '--model' => 'App\Models\User',
            '--table' => 'users',
            '--path' => $testDir,
            '--force' => true,
        ])->assertExitCode(Command::SUCCESS);
    });

    it('creates yaml file with custom path', function () {
        $testDir = base_path('test-dto-definitions');

        $this->artisan('dto:definition-init', [
            'name' => 'UserDTO',
            '--model' => 'App\Models\User',
            '--table' => 'users',
            '--path' => $testDir,
        ])->assertExitCode(Command::SUCCESS);

        expect(File::exists($testDir.'/user.yaml'))->toBeTrue();
    });

    it('creates yaml file with default path when no path option is provided', function () {
        $this->artisan('dto:definition-init', [
            'name' => 'UserDTO',
            '--model' => 'App\Models\User',
            '--table' => 'users',
        ])->assertExitCode(Command::SUCCESS);

        // Check what path was actually used (from test config)
        $defaultPath = config('dto.definitions_path', base_path('tests/stubs/dto_definitions'));
        $expectedFile = $defaultPath.'/user.yaml';

        // Verify the file was created in the default location
        expect(File::exists($expectedFile))->toBeTrue();
    });
});
