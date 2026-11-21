<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

beforeEach(function () {
    BehavioralTraitRegistry::registerDefaults();

    $this->definitionPath = __DIR__.'/temp_openapi_definitions';
    $this->outputPath = __DIR__.'/temp_openapi_output';
    $this->outputFile = $this->outputPath.'/schemas.json';

    File::deleteDirectory($this->definitionPath);
    File::deleteDirectory($this->outputPath);
    File::ensureDirectoryExists($this->definitionPath);

    config()->set('dto.definitions_path', $this->definitionPath);
    config()->set('dto.openapi_path', $this->outputFile);

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
    rules: [min:3, max:20]
  product:
    type: dto
    dto: ProductDTO
relations:
  customer:
    type: belongsTo
    dto: CustomerDTO
YAML);
});

afterEach(function () {
    File::deleteDirectory($this->definitionPath);
    File::deleteDirectory($this->outputPath);
});

it('writes an OpenAPI document with every definition', function () {
    $exitCode = Artisan::call('dto:generate-openapi');

    expect($exitCode)->toBe(0);
    expect(Artisan::output())->toContain('Schema CustomerDTO is referenced but has no YAML definition');

    $document = json_decode(File::get($this->outputFile), true);

    expect($document['openapi'])->toBe('3.1.0');
    expect($document['info'])->toBe(['title' => 'DTO Schemas', 'version' => '1.0.0']);
    expect(array_keys($document['components']['schemas']))->toBe(['OrderDTO', 'ProductDTO']);

    expect($document['components']['schemas']['ProductDTO'])->toBe([
        'type' => 'object',
        'properties' => [
            'id' => ['type' => 'string', 'format' => 'uuid'],
            'name' => ['type' => 'string'],
            'description' => ['type' => ['string', 'null'], 'maxLength' => 500],
            'price' => ['type' => 'number', 'format' => 'float', 'minimum' => 0],
            'tags' => ['type' => ['array', 'null'], 'items' => [], 'uniqueItems' => true],
            'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'archived']],
            'available' => ['type' => 'boolean'],
            'created_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
            'updated_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
            'deleted_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
        ],
        'required' => ['id', 'name', 'price', 'status', 'available'],
    ]);

    expect($document['components']['schemas']['OrderDTO']['properties'])->toBe([
        'reference' => ['type' => 'string', 'minLength' => 3, 'maxLength' => 20],
        'product' => ['$ref' => '#/components/schemas/ProductDTO'],
        'customer' => ['$ref' => '#/components/schemas/CustomerDTO'],
    ]);
});

it('exports only the given definitions', function () {
    $exitCode = Artisan::call('dto:generate-openapi', ['filenames' => ['full-featured.yaml']]);

    expect($exitCode)->toBe(0);

    $document = json_decode(File::get($this->outputFile), true);
    expect(array_keys($document['components']['schemas']))->toBe(['ProductDTO']);
});

it('writes YAML, OpenAPI 3.0 and custom info', function () {
    $output = $this->outputPath.'/openapi.yaml';

    $exitCode = Artisan::call('dto:generate-openapi', [
        '--output' => $output,
        '--openapi' => '3.0',
        '--title' => 'Shop API',
        '--api-version' => '2.0.0',
    ]);

    expect($exitCode)->toBe(0);

    $document = Yaml::parse(File::get($output));
    expect($document['openapi'])->toBe('3.0.3');
    expect($document['info'])->toBe(['title' => 'Shop API', 'version' => '2.0.0']);
    expect($document['components']['schemas']['ProductDTO']['properties']['description'])
        ->toBe(['type' => 'string', 'maxLength' => 500, 'nullable' => true]);
});

it('derives a .yaml default path for --format=yaml', function () {
    $exitCode = Artisan::call('dto:generate-openapi', ['--format' => 'yaml']);

    expect($exitCode)->toBe(0);
    expect(File::exists($this->outputPath.'/schemas.yaml'))->toBeTrue();
    expect(File::exists($this->outputFile))->toBeFalse();
});

it('writes only the components object with --components-only', function () {
    Artisan::call('dto:generate-openapi', ['--components-only' => true]);

    $document = json_decode(File::get($this->outputFile), true);

    expect(array_keys($document))->toBe(['components']);
    expect($document['components']['schemas'])->toHaveKeys(['OrderDTO', 'ProductDTO']);
});

it('does not overwrite an existing file without --force', function () {
    File::ensureDirectoryExists($this->outputPath);
    File::put($this->outputFile, 'existing');

    expect(Artisan::call('dto:generate-openapi'))->toBe(1);
    expect(File::get($this->outputFile))->toBe('existing');

    expect(Artisan::call('dto:generate-openapi', ['--force' => true]))->toBe(0);
    expect(File::get($this->outputFile))->toContain('"openapi": "3.1.0"');
});

it('prints the document without writing on --dry-run', function () {
    $exitCode = Artisan::call('dto:generate-openapi', ['--dry-run' => true]);

    expect($exitCode)->toBe(0);
    expect(Artisan::output())->toContain('"ProductDTO": {');
    expect(File::exists($this->outputFile))->toBeFalse();
});

it('detects drift with --check', function () {
    expect(Artisan::call('dto:generate-openapi', ['--check' => true]))->toBe(1);
    expect(Artisan::output())->toContain('OpenAPI file is missing');

    Artisan::call('dto:generate-openapi');

    expect(Artisan::call('dto:generate-openapi', ['--check' => true]))->toBe(0);
    expect(Artisan::output())->toContain('OpenAPI file is up to date');

    File::put($this->outputFile, File::get($this->outputFile).' ');

    expect(Artisan::call('dto:generate-openapi', ['--check' => true]))->toBe(1);
    expect(Artisan::output())->toContain('OpenAPI file is out of date');
});

it('fails when a YAML file does not exist', function () {
    expect(Artisan::call('dto:generate-openapi', ['filenames' => ['missing.yaml']]))->toBe(1);
    expect(Artisan::output())->toContain('YAML file not found');
});

it('fails when no definition exists', function () {
    File::cleanDirectory($this->definitionPath);

    expect(Artisan::call('dto:generate-openapi'))->toBe(1);
    expect(Artisan::output())->toContain('No YAML files found');
});

it('fails on an unsupported format or OpenAPI version', function () {
    expect(Artisan::call('dto:generate-openapi', ['--format' => 'xml']))->toBe(1);
    expect(Artisan::output())->toContain('Unsupported format: xml');

    expect(Artisan::call('dto:generate-openapi', ['--openapi' => '2.0']))->toBe(1);
    expect(Artisan::output())->toContain('Unsupported OpenAPI version');
});

it('fails when a definition has no DTO name', function () {
    File::put($this->definitionPath.'/broken.yaml', "fields:\n  name:\n    type: string\n");

    expect(Artisan::call('dto:generate-openapi'))->toBe(1);
    expect(Artisan::output())->toContain('no DTO name');
});
