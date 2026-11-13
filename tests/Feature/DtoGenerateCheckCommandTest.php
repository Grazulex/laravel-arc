<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    BehavioralTraitRegistry::registerDefaults();

    $this->definitionPath = __DIR__.'/temp_check_definitions';
    $this->outputPath = __DIR__.'/temp_check_output';

    File::deleteDirectory($this->definitionPath);
    File::deleteDirectory($this->outputPath);
    File::ensureDirectoryExists($this->definitionPath);
    File::ensureDirectoryExists($this->outputPath);

    config()->set('dto.definitions_path', $this->definitionPath);
    config()->set('dto.output_path', $this->outputPath);
    config()->set('dto.namespace', 'App\\DTO');

    File::copy(
        __DIR__.'/DtoGenerator/fixtures/full-featured.yaml',
        $this->definitionPath.'/full-featured.yaml'
    );

    $this->dtoPath = $this->outputPath.'/ProductDTO.php';
});

afterEach(function () {
    File::deleteDirectory($this->definitionPath);
    File::deleteDirectory($this->outputPath);
});

it('passes --check when the generated file is up to date', function () {
    Artisan::call('dto:generate', [
        'filename' => 'full-featured.yaml',
        '--force' => true,
        '--output' => $this->dtoPath,
    ]);

    $exitCode = Artisan::call('dto:generate', [
        'filename' => 'full-featured.yaml',
        '--output' => $this->dtoPath,
        '--check' => true,
    ]);

    expect($exitCode)->toBe(0);
    expect(Artisan::output())->toContain('DTO file is up to date');
});

it('fails --check when the generated file differs from the YAML definition', function () {
    Artisan::call('dto:generate', [
        'filename' => 'full-featured.yaml',
        '--force' => true,
        '--output' => $this->dtoPath,
    ]);

    $drifted = File::get($this->dtoPath)."\n// manual edit\n";
    File::put($this->dtoPath, $drifted);

    $exitCode = Artisan::call('dto:generate', [
        'filename' => 'full-featured.yaml',
        '--output' => $this->dtoPath,
        '--check' => true,
    ]);

    expect($exitCode)->toBe(1);
    expect(Artisan::output())->toContain('DTO file is out of date');
    // The existing file must not be touched
    expect(File::get($this->dtoPath))->toBe($drifted);
});

it('fails --check when the generated file does not exist', function () {
    $exitCode = Artisan::call('dto:generate', [
        'filename' => 'full-featured.yaml',
        '--output' => $this->dtoPath,
        '--check' => true,
    ]);

    expect($exitCode)->toBe(1);
    expect(Artisan::output())->toContain('DTO file is missing');
    expect(File::exists($this->dtoPath))->toBeFalse();
});

it('ignores line ending differences with --check', function () {
    Artisan::call('dto:generate', [
        'filename' => 'full-featured.yaml',
        '--force' => true,
        '--output' => $this->dtoPath,
    ]);

    File::put($this->dtoPath, str_replace("\n", "\r\n", File::get($this->dtoPath)));

    $exitCode = Artisan::call('dto:generate', [
        'filename' => 'full-featured.yaml',
        '--output' => $this->dtoPath,
        '--check' => true,
    ]);

    expect($exitCode)->toBe(0);
});

it('fails --check with --all when any generated file is out of date', function () {
    Artisan::call('dto:generate', [
        'filename' => 'full-featured.yaml',
        '--force' => true,
        '--output' => $this->dtoPath,
    ]);

    File::put($this->dtoPath, File::get($this->dtoPath).'// drift');

    $exitCode = Artisan::call('dto:generate', [
        '--all' => true,
        '--output' => $this->dtoPath,
        '--check' => true,
    ]);

    expect($exitCode)->toBe(1);
    expect(Artisan::output())->toContain('out of date or could not be checked');
});

it('passes --check with --all when every generated file is up to date', function () {
    Artisan::call('dto:generate', [
        'filename' => 'full-featured.yaml',
        '--force' => true,
        '--output' => $this->dtoPath,
    ]);

    $exitCode = Artisan::call('dto:generate', [
        '--all' => true,
        '--output' => $this->dtoPath,
        '--check' => true,
    ]);

    expect($exitCode)->toBe(0);
    expect(Artisan::output())->toContain('All DTO files are up to date');
});
