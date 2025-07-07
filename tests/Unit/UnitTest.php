<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Tests\Unit;

use Grazulex\LaravelArc\Console\Commands\DtoDefinitionInitCommand;
use Grazulex\LaravelArc\Support\DtoPaths;

use Illuminate\Support\Facades\File;

beforeEach(function () {
    File::ensureDirectoryExists(DtoPaths::definitionDir());
    File::ensureDirectoryExists(DtoPaths::dtoOutputDir());
});

it('initializes a DTO YAML definition file', function () {
    $command = new DtoDefinitionInitCommand();
    $command->setLaravel(app());

    $name = 'UserDTO';
    $model = 'App\\Models\\User';
    $table = 'users';

    $this->artisan('dto:definition-init', [
        'name' => $name,
        '--model' => $model,
        '--table' => $table,
    ])
        ->expectsOutput("DTO YAML created at: ".DtoPaths::definitionFilePath($name))
        ->assertExitCode(0);

    expect(File::exists(DtoPaths::definitionFilePath($name)))->toBeTrue();
});