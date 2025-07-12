<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\Traits\ConvertsData;
use Grazulex\LaravelArc\Support\Traits\DtoUtilities;
use Grazulex\LaravelArc\Support\Traits\ValidatesData;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Clean up any test files
    File::deleteDirectory(base_path('temp_test_traits'));
    File::deleteDirectory(base_path('vendor/orchestra/testbench-core/laravel/temp_test_traits'));
});

afterEach(function () {
    // Clean up any test files
    File::deleteDirectory(base_path('temp_test_traits'));
    File::deleteDirectory(base_path('vendor/orchestra/testbench-core/laravel/temp_test_traits'));
});

it('generates DTO with traits included', function () {
    $testDir = base_path('temp_test_traits');
    File::ensureDirectoryExists($testDir);

    // Create test YAML file
    $yaml = <<<YAML
header:
  dto: TraitTestDTO
  model: App\Models\User

fields:
  name:
    type: string
    required: true
    rules: [min:2]
  email:
    type: string
    required: true
    rules: [email]

options:
  timestamps: false
  namespace: App\DTOs
YAML;

    File::put($testDir.'/trait-test.yaml', $yaml);

    // Configure paths
    config(['dto.definitions_path' => $testDir]);
    config(['dto.output_path' => base_path('temp_test_traits_output')]);

    // Generate DTO
    $result = Artisan::call('dto:generate', [
        'filename' => 'trait-test.yaml',
    ]);

    expect($result)->toBe(0);

    // Get the output to find where the file was actually created
    $output = Artisan::output();
    $outputLines = explode("\n", $output);
    $pathLine = collect($outputLines)->first(fn ($line) => str_contains($line, 'DTO class written to:'));

    expect($pathLine)->not->toBeNull();

    $actualPath = mb_trim(str_replace('✅ DTO class written to:', '', $pathLine));
    expect(File::exists($actualPath))->toBeTrue();

    // Read generated file content
    $content = File::get($actualPath);

    // Check that traits are included
    expect($content)->toContain('use Grazulex\LaravelArc\Support\Traits\ConvertsData;');
    expect($content)->toContain('use Grazulex\LaravelArc\Support\Traits\DtoUtilities;');
    expect($content)->toContain('use Grazulex\LaravelArc\Support\Traits\ValidatesData;');
    expect($content)->toContain('use ConvertsData;');
    expect($content)->toContain('use DtoUtilities;');
    expect($content)->toContain('use ValidatesData;');

    // Check that validate() method is NOT generated (since it's in the trait)
    expect($content)->not->toContain('public static function validate(array $data): \\Illuminate\\Contracts\\Validation\\Validator');

    // Check that rules() method is still generated
    expect($content)->toContain('public static function rules(): array');
});

it('validates trait ValidatesData functionality', function () {
    // Test the ValidatesData trait
    $trait = new class
    {
        use ValidatesData;

        public static function rules(): array
        {
            return [
                'name' => ['required', 'string', 'min:2'],
                'email' => ['required', 'email'],
            ];
        }
    };

    // Test valid data
    $validData = ['name' => 'John', 'email' => 'john@example.com'];
    expect($trait::passes($validData))->toBeTrue();
    expect($trait::fails($validData))->toBeFalse();

    // Test invalid data
    $invalidData = ['name' => 'J', 'email' => 'invalid-email'];
    expect($trait::passes($invalidData))->toBeFalse();
    expect($trait::fails($invalidData))->toBeTrue();
});

it('validates trait ConvertsData functionality', function () {
    // Create a mock DTO class with ConvertsData trait
    $dto = new class('test', 'test@example.com')
    {
        use ConvertsData;

        public function __construct(
            public readonly string $name,
            public readonly string $email,
        ) {}

        public static function fromModel($model): self
        {
            return new self($model->name, $model->email);
        }

        public function toArray(): array
        {
            return [
                'name' => $this->name,
                'email' => $this->email,
            ];
        }
    };

    // Test toJson
    $json = $dto->toJson();
    expect($json)->toBeJson();
    expect(json_decode($json, true))->toBe(['name' => 'test', 'email' => 'test@example.com']);

    // Test toCollection - should return DTOCollection
    $collection = $dto->toCollection();
    expect($collection)->toBeInstanceOf(\Grazulex\LaravelArc\Support\DTOCollection::class);
    expect($collection->toArray())->toBe(['name' => 'test', 'email' => 'test@example.com']);

    // Test only
    expect($dto->only(['name']))->toBe(['name' => 'test']);

    // Test except
    expect($dto->except(['email']))->toBe(['name' => 'test']);
});

it('validates trait DtoUtilities functionality', function () {
    // Create a mock DTO class with DtoUtilities trait
    $dto = new class('test', 'test@example.com')
    {
        use DtoUtilities;

        public function __construct(
            public readonly string $name,
            public readonly string $email,
        ) {}

        public function toArray(): array
        {
            return [
                'name' => $this->name,
                'email' => $this->email,
            ];
        }
    };

    // Test getProperties
    $properties = $dto->getProperties();
    expect($properties)->toContain('name');
    expect($properties)->toContain('email');

    // Test hasProperty
    expect($dto->hasProperty('name'))->toBeTrue();
    expect($dto->hasProperty('nonexistent'))->toBeFalse();

    // Test getProperty
    expect($dto->getProperty('name'))->toBe('test');

    // Test equals
    $dto2 = new ($dto::class)('test', 'test@example.com');
    $dto3 = new ($dto::class)('different', 'test@example.com');
    expect($dto->equals($dto2))->toBeTrue();
    expect($dto->equals($dto3))->toBeFalse();
});
