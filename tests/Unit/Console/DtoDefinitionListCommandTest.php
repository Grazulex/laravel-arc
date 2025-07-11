<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

describe('DtoDefinitionListCommand', function () {
    beforeEach(function () {
        // Clear any previous test files
        File::deleteDirectory(base_path('test-dto-definitions'));
    });

    afterEach(function () {
        // Clean up test files
        File::deleteDirectory(base_path('test-dto-definitions'));
    });

    it('fails when directory does not exist', function () {
        $this->artisan('dto:definition-list', [
            '--path' => base_path('non-existent-directory'),
        ])->assertExitCode(Command::FAILURE);
    });

    it('shows warning when no yaml files are found', function () {
        $testDir = base_path('test-dto-definitions');
        File::ensureDirectoryExists($testDir);

        $this->artisan('dto:definition-list', [
            '--path' => $testDir,
        ])->assertExitCode(Command::SUCCESS);
    });

    it('lists yaml files with compact option', function () {
        $testDir = base_path('test-dto-definitions');
        File::ensureDirectoryExists($testDir);

        // Create a test YAML file
        File::put($testDir.'/user.yaml', '
dto: UserDTO
model: App\\Models\\User
table: users
fields:
  - name: id
    type: integer
  - name: email
    type: string
');

        $this->artisan('dto:definition-list', [
            '--path' => $testDir,
            '--compact' => true,
        ])->assertExitCode(Command::SUCCESS);
    });

    it('lists yaml files with full details', function () {
        $testDir = base_path('test-dto-definitions');
        File::ensureDirectoryExists($testDir);

        // Create a test YAML file
        File::put($testDir.'/user.yaml', '
dto: UserDTO
model: App\\Models\\User
table: users
fields:
  - name: id
    type: integer
  - name: email
    type: string
relations:
  - name: posts
    type: hasMany
');

        $this->artisan('dto:definition-list', [
            '--path' => $testDir,
        ])->assertExitCode(Command::SUCCESS);
    });

    it('outputs json when --json flag is used', function () {
        $testDir = base_path('test-dto-definitions');
        File::ensureDirectoryExists($testDir);

        // Create a test YAML file
        File::put($testDir.'/user.yaml', '
header:
  dto: UserDTO
  model: App\\Models\\User
  table: users
fields:
  - name: id
    type: integer
  - name: email
    type: string
relations:
  - name: posts
    type: hasMany
');

        $result = $this->artisan('dto:definition-list', [
            '--path' => $testDir,
            '--json' => true,
        ])->assertExitCode(Command::SUCCESS);

        $output = $result->getOutput();
        $jsonData = json_decode($output, true);

        expect($jsonData)->toBeArray();
        expect($jsonData)->toHaveCount(1);
        expect($jsonData[0])->toHaveKeys(['dto', 'model', 'table', 'fields', 'relations', 'dtoExists', 'yamlFile', 'dtoPath']);
        expect($jsonData[0]['dto'])->toBe('UserDTO');
        expect($jsonData[0]['model'])->toBe('App\\Models\\User');
        expect($jsonData[0]['table'])->toBe('users');
        expect($jsonData[0]['yamlFile'])->toBe('user.yaml');
    });

    it('outputs empty json array when no yaml files and --json flag is used', function () {
        $testDir = base_path('test-dto-definitions');
        File::ensureDirectoryExists($testDir);

        $result = $this->artisan('dto:definition-list', [
            '--path' => $testDir,
            '--json' => true,
        ])->assertExitCode(Command::SUCCESS);

        $output = $result->getOutput();
        $jsonData = json_decode($output, true);

        expect($jsonData)->toBeArray();
        expect($jsonData)->toHaveCount(0);
    });

    it('outputs json error when directory does not exist and --json flag is used', function () {
        $result = $this->artisan('dto:definition-list', [
            '--path' => base_path('non-existent-directory'),
            '--json' => true,
        ])->assertExitCode(Command::FAILURE);

        $output = $result->getOutput();
        $jsonData = json_decode($output, true);

        expect($jsonData)->toBeArray();
        expect($jsonData)->toHaveKey('error');
    });
});
