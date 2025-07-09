<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

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
