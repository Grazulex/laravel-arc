<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    BehavioralTraitRegistry::registerDefaults();

    $this->definitionPath = __DIR__.'/temp_validate_definitions';
    $this->outputPath = __DIR__.'/temp_validate_output';

    File::deleteDirectory($this->definitionPath);
    File::deleteDirectory($this->outputPath);
    File::ensureDirectoryExists($this->definitionPath);

    config()->set('dto.definitions_path', $this->definitionPath);
    config()->set('dto.output_path', $this->outputPath);
    config()->set('dto.namespace', 'App\\DTO');

    File::copy(
        __DIR__.'/DtoGenerator/fixtures/full-featured.yaml',
        $this->definitionPath.'/full-featured.yaml'
    );
});

afterEach(function () {
    File::deleteDirectory($this->definitionPath);
    File::deleteDirectory($this->outputPath);
});

function writeValidateDefinition(string $directory, string $filename, string $content): void
{
    File::put($directory.'/'.$filename, $content);
}

it('is registered as an artisan command', function () {
    expect(array_keys(Artisan::all()))->toContain('dto:definition-validate');
});

it('validates a single valid definition without generating anything', function () {
    $exitCode = Artisan::call('dto:definition-validate', ['filenames' => ['full-featured.yaml']]);
    $output = Artisan::output();

    expect($exitCode)->toBe(0);
    expect($output)->toContain('full-featured.yaml (ProductDTO)');
    expect($output)->toContain('1 definition(s) valid');
    expect(File::exists($this->outputPath))->toBeFalse();
});

it('accepts a filename without its extension', function () {
    $exitCode = Artisan::call('dto:definition-validate', ['filenames' => ['full-featured']]);

    expect($exitCode)->toBe(0);
    expect(Artisan::output())->toContain('full-featured.yaml (ProductDTO)');
});

it('reports the missing header model as a warning only', function () {
    $exitCode = Artisan::call('dto:definition-validate', ['filenames' => ['full-featured.yaml']]);

    expect($exitCode)->toBe(0);
    expect(Artisan::output())->toContain("Header model 'App\\Models\\Product' does not exist");
});

it('requires a filename or --all', function () {
    $exitCode = Artisan::call('dto:definition-validate');

    expect($exitCode)->toBe(1);
    expect(Artisan::output())->toContain('Please provide a YAML filename or use --all');
});

it('fails when the definitions directory does not exist', function () {
    $exitCode = Artisan::call('dto:definition-validate', [
        '--all' => true,
        '--path' => $this->definitionPath.'/missing',
    ]);

    expect($exitCode)->toBe(1);
    expect(Artisan::output())->toContain('Directory not found');
});

it('fails with --all when the directory has no definition', function () {
    File::delete($this->definitionPath.'/full-featured.yaml');

    $exitCode = Artisan::call('dto:definition-validate', ['--all' => true]);

    expect($exitCode)->toBe(1);
    expect(Artisan::output())->toContain('No YAML files found in directory');
});

it('fails when the requested file does not exist', function () {
    $exitCode = Artisan::call('dto:definition-validate', ['filenames' => ['unknown.yaml']]);

    expect($exitCode)->toBe(1);
    expect(Artisan::output())->toContain('File not found');
});

it('reports an unknown field type with a suggestion', function () {
    writeValidateDefinition($this->definitionPath, 'broken.yaml', <<<'YAML'
header:
  dto: BrokenDTO
fields:
  title:
    type: strng
YAML);

    $exitCode = Artisan::call('dto:definition-validate', ['filenames' => ['broken.yaml']]);
    $output = Artisan::output();

    expect($exitCode)->toBe(1);
    expect($output)->toContain("Field 'title': unknown type 'strng'");
    expect($output)->toContain("Did you mean 'string'?");
    expect($output)->toContain('1 of 1 definition(s) invalid');
});

it('reports a relation to a missing model', function () {
    writeValidateDefinition($this->definitionPath, 'broken.yaml', <<<'YAML'
header:
  dto: BrokenDTO
fields:
  title:
    type: string
relations:
  category:
    type: belongsTo
    model: App\Models\MissingCategory
YAML);

    $exitCode = Artisan::call('dto:definition-validate', ['filenames' => ['broken.yaml']]);

    expect($exitCode)->toBe(1);
    expect(Artisan::output())->toContain("Relation 'category': model 'App\\Models\\MissingCategory' does not exist");
});

it('reports an unknown relation type and a relation without target', function () {
    writeValidateDefinition($this->definitionPath, 'broken.yaml', <<<'YAML'
header:
  dto: BrokenDTO
fields:
  title:
    type: string
relations:
  category:
    type: belongTo
    dto: ProductDTO
  owner:
    type: hasOne
YAML);

    $exitCode = Artisan::call('dto:definition-validate', ['filenames' => ['broken.yaml']]);
    $output = Artisan::output();

    expect($exitCode)->toBe(1);
    expect($output)->toContain("Relation 'category': unknown relation type 'belongTo'");
    expect($output)->toContain("Did you mean 'belongsTo'?");
    expect($output)->toContain("Relation 'owner': no target declared");
});

it('reports incorrect validation rules', function () {
    writeValidateDefinition($this->definitionPath, 'broken.yaml', <<<'YAML'
header:
  dto: BrokenDTO
fields:
  title:
    type: string
    rules: [requird, 'min:', 'max:abc', 'between:1']
  code:
    type: string
    validation: ['regex:/[a-z', 'in_enum']
  slug:
    type: string
    rules: required
YAML);

    $exitCode = Artisan::call('dto:definition-validate', ['filenames' => ['broken.yaml']]);
    $output = Artisan::output();

    expect($exitCode)->toBe(1);
    expect($output)->toContain("Field 'title': unknown validation rule 'requird'");
    expect($output)->toContain("Did you mean 'required'?");
    expect($output)->toContain("Field 'title': validation rule 'min' requires at least 1 parameter(s)");
    expect($output)->toContain("Field 'title': validation rule 'max:abc' expects numeric parameters");
    expect($output)->toContain("Field 'title': validation rule 'between' requires at least 2 parameter(s)");
    expect($output)->toContain("Field 'code': validation rule 'regex:/[a-z' is not a valid regular expression");
    expect($output)->toContain("Field 'slug': 'rules' must be a list of validation rules");
    expect($output)->not->toContain("unknown validation rule 'in_enum'");
});

it('reports header problems', function () {
    writeValidateDefinition($this->definitionPath, 'no-header.yaml', <<<'YAML'
dto: LegacyDTO
fields:
  title:
    type: string
YAML);

    writeValidateDefinition($this->definitionPath, 'bad-header.yaml', <<<'YAML'
header:
  dto: BadHeaderDTO
  namespace: App\\Invalid Namespace
  traits: [HasTimestamp]
fields:
  title:
    type: string
YAML);

    $exitCode = Artisan::call('dto:definition-validate', ['filenames' => ['no-header.yaml', 'bad-header.yaml']]);
    $output = Artisan::output();

    expect($exitCode)->toBe(1);
    expect($output)->toContain("Missing 'header' section");
    expect($output)->toContain("Move the top-level 'dto' key under 'header'");
    expect($output)->toContain('Invalid namespace');
    expect($output)->toContain("Unknown behavioral trait 'HasTimestamp'");
    expect($output)->toContain("Did you mean 'HasTimestamps'?");
    expect($output)->toContain('2 of 2 definition(s) invalid');
});

it('reports invalid YAML syntax and multiple documents', function () {
    writeValidateDefinition($this->definitionPath, 'syntax.yaml', "header:\n  dto: [unclosed\n");

    File::copy(
        __DIR__.'/DtoGenerator/fixtures/multiple-documents-test.yaml',
        $this->definitionPath.'/multiple.yaml'
    );

    $exitCode = Artisan::call('dto:definition-validate', ['filenames' => ['syntax.yaml', 'multiple.yaml']]);
    $output = Artisan::output();

    expect($exitCode)->toBe(1);
    expect($output)->toContain('Invalid YAML syntax');
    expect($output)->toContain('Multiple YAML documents detected');
});

it('resolves nested DTOs and relations against the other definitions', function () {
    writeValidateDefinition($this->definitionPath, 'order.yaml', <<<'YAML'
header:
  dto: OrderDTO
  namespace: App\DTO
fields:
  reference:
    type: string
    rules: [min:3, max:20]
  product:
    type: dto
    dto: ProductDTO
  ghost:
    type: dto
    dto: GhostDTO
relations:
  product_relation:
    type: belongsTo
    dto: ProductDTO
  customer:
    type: hasOne
    dto: CustomerDTO
YAML);

    $exitCode = Artisan::call('dto:definition-validate', ['filenames' => ['order.yaml']]);
    $output = Artisan::output();

    expect($exitCode)->toBe(1);
    expect($output)->toContain("Field 'ghost': DTO 'GhostDTO' has no YAML definition");
    expect($output)->toContain("Relation 'customer': DTO 'CustomerDTO' has no YAML definition");
    expect($output)->not->toContain("DTO 'ProductDTO' has no YAML definition");
});

it('validates every definition with --all and fails when one is invalid', function () {
    writeValidateDefinition($this->definitionPath, 'broken.yaml', <<<'YAML'
header:
  dto: BrokenDTO
fields:
  title:
    type: nope
YAML);

    $exitCode = Artisan::call('dto:definition-validate', ['--all' => true]);
    $output = Artisan::output();

    expect($exitCode)->toBe(1);
    expect($output)->toContain('full-featured.yaml (ProductDTO)');
    expect($output)->toContain('broken.yaml (BrokenDTO)');
    expect($output)->toContain("unknown type 'nope'");
    expect($output)->toContain('1 of 2 definition(s) invalid');
    expect(File::exists($this->outputPath))->toBeFalse();
});

it('passes with --all when every definition is valid', function () {
    $exitCode = Artisan::call('dto:definition-validate', ['--all' => true]);

    expect($exitCode)->toBe(0);
    expect(Artisan::output())->toContain('1 definition(s) valid');
});

it('supports the --path option', function () {
    $customPath = $this->definitionPath.'/custom';
    File::ensureDirectoryExists($customPath);

    writeValidateDefinition($customPath, 'broken.yaml', <<<'YAML'
header:
  dto: BrokenDTO
fields:
  title:
    type: nope
YAML);

    $exitCode = Artisan::call('dto:definition-validate', ['--all' => true, '--path' => $customPath]);
    $output = Artisan::output();

    expect($exitCode)->toBe(1);
    expect($output)->toContain('broken.yaml (BrokenDTO)');
    expect($output)->not->toContain('full-featured.yaml');
});

it('outputs the results as JSON', function () {
    writeValidateDefinition($this->definitionPath, 'broken.yaml', <<<'YAML'
header:
  dto: BrokenDTO
fields:
  title:
    type: nope
YAML);

    $exitCode = Artisan::call('dto:definition-validate', ['--all' => true, '--json' => true]);
    $report = json_decode(Artisan::output(), true);

    expect($exitCode)->toBe(1);
    expect($report['valid'])->toBeFalse();
    expect($report['files'])->toHaveCount(2);

    $byFile = collect($report['files'])->keyBy('file');

    expect($byFile['broken.yaml']['valid'])->toBeFalse();
    expect($byFile['broken.yaml']['dto'])->toBe('BrokenDTO');
    expect($byFile['broken.yaml']['errors'][0])->toContain("unknown type 'nope'");
    expect($byFile['full-featured.yaml']['valid'])->toBeTrue();
    expect($byFile['full-featured.yaml']['errors'])->toBe([]);
});
