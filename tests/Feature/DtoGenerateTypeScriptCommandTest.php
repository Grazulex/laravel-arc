<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    BehavioralTraitRegistry::registerDefaults();

    $this->definitionPath = __DIR__.'/temp_ts_definitions';
    $this->outputPath = __DIR__.'/temp_ts_output';

    File::deleteDirectory($this->definitionPath);
    File::deleteDirectory($this->outputPath);
    File::ensureDirectoryExists($this->definitionPath);

    config()->set('dto.definitions_path', $this->definitionPath);
    config()->set('dto.typescript_path', $this->outputPath);

    File::copy(
        __DIR__.'/DtoGenerator/fixtures/full-featured.yaml',
        $this->definitionPath.'/full-featured.yaml'
    );

    File::put($this->definitionPath.'/order.yaml', <<<'YAML'
header:
  dto: OrderDTO
fields:
  reference:
    type: string
  product:
    type: dto
    dto: ProductDTO
YAML);

    $this->productTs = $this->outputPath.'/ProductDTO.ts';
});

afterEach(function () {
    File::deleteDirectory($this->definitionPath);
    File::deleteDirectory($this->outputPath);
});

it('writes a TypeScript interface for a YAML definition', function () {
    $exitCode = Artisan::call('dto:generate-ts', ['filename' => 'full-featured.yaml']);

    expect($exitCode)->toBe(0);
    expect(File::exists($this->productTs))->toBeTrue();

    $expected = <<<'TS'
export interface ProductDTO {
  id: string;
  name: string;
  description: string | null;
  price: number;
  tags: unknown[] | null;
  status: 'draft' | 'published' | 'archived';
  available: boolean;
  created_at: string | null;
  updated_at: string | null;
  deleted_at: string | null;
}

TS;

    expect(File::get($this->productTs))->toBe($expected);
});

it('imports nested DTO types', function () {
    Artisan::call('dto:generate-ts', ['filename' => 'order.yaml']);

    expect(File::get($this->outputPath.'/OrderDTO.ts'))
        ->toStartWith("import type { ProductDTO } from './ProductDTO';\n\nexport interface OrderDTO {")
        ->toContain('product: ProductDTO;');
});

it('emits type aliases with --type', function () {
    Artisan::call('dto:generate-ts', ['filename' => 'order.yaml', '--type' => true]);

    expect(File::get($this->outputPath.'/OrderDTO.ts'))->toContain('export type OrderDTO = {');
});

it('generates every definition with --all into a custom path', function () {
    $customPath = $this->outputPath.'/custom';

    $exitCode = Artisan::call('dto:generate-ts', ['--all' => true, '--path' => $customPath]);

    expect($exitCode)->toBe(0);
    expect(File::exists($customPath.'/ProductDTO.ts'))->toBeTrue();
    expect(File::exists($customPath.'/OrderDTO.ts'))->toBeTrue();
});

it('does not overwrite existing files without --force', function () {
    File::ensureDirectoryExists($this->outputPath);
    File::put($this->productTs, 'existing');

    $exitCode = Artisan::call('dto:generate-ts', ['filename' => 'full-featured.yaml']);

    expect($exitCode)->toBe(1);
    expect(File::get($this->productTs))->toBe('existing');

    $exitCode = Artisan::call('dto:generate-ts', ['filename' => 'full-featured.yaml', '--force' => true]);

    expect($exitCode)->toBe(0);
    expect(File::get($this->productTs))->toContain('export interface ProductDTO');
});

it('prints the result without writing on --dry-run', function () {
    $exitCode = Artisan::call('dto:generate-ts', ['filename' => 'full-featured.yaml', '--dry-run' => true]);

    expect($exitCode)->toBe(0);
    expect(Artisan::output())->toContain('export interface ProductDTO');
    expect(File::exists($this->productTs))->toBeFalse();
});

it('detects drift with --check', function () {
    $exitCode = Artisan::call('dto:generate-ts', ['filename' => 'full-featured.yaml', '--check' => true]);
    expect($exitCode)->toBe(1);
    expect(Artisan::output())->toContain('TypeScript file is missing');

    Artisan::call('dto:generate-ts', ['--all' => true]);

    $exitCode = Artisan::call('dto:generate-ts', ['--all' => true, '--check' => true]);
    expect($exitCode)->toBe(0);
    expect(Artisan::output())->toContain('All TypeScript files are up to date');

    File::put($this->productTs, File::get($this->productTs).'// drift');

    $exitCode = Artisan::call('dto:generate-ts', ['filename' => 'full-featured.yaml', '--check' => true]);
    expect($exitCode)->toBe(1);
    expect(Artisan::output())->toContain('TypeScript file is out of date');
});

it('fails when the YAML file does not exist', function () {
    $exitCode = Artisan::call('dto:generate-ts', ['filename' => 'missing.yaml']);

    expect($exitCode)->toBe(1);
    expect(Artisan::output())->toContain('YAML file not found');
});

it('fails without a filename or --all', function () {
    $exitCode = Artisan::call('dto:generate-ts');

    expect($exitCode)->toBe(1);
    expect(Artisan::output())->toContain('Please provide a YAML filename or use --all');
});

it('fails when a definition has no DTO name', function () {
    File::put($this->definitionPath.'/broken.yaml', "fields:\n  name:\n    type: string\n");

    $exitCode = Artisan::call('dto:generate-ts', ['filename' => 'broken.yaml']);

    expect($exitCode)->toBe(1);
    expect(Artisan::output())->toContain('Failed to export broken.yaml');
});
