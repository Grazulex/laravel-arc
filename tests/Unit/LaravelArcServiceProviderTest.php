<?php

declare(strict_types=1);

use Grazulex\LaravelArc\LaravelArcServiceProvider;
use Illuminate\Support\Facades\Validator;

beforeEach(function () {
    $this->provider = new LaravelArcServiceProvider($this->app);
});

it('registers config and commands', function () {
    // Should not throw any exception
    $this->provider->register();

    // Check that config is merged
    expect(config('dto'))->toBeArray();
    expect(config('dto.output_path'))->toBeString();
});

it('publishes config when boot is called', function () {
    $this->provider->boot();

    // Check that config is published
    $publishes = $this->provider::pathsToPublish();
    expect($publishes)->toBeArray();
    expect($publishes)->not->toBeEmpty();
});

it('registers custom validation rules', function () {
    $this->provider->boot();

    // Test in_enum rule
    $validator = Validator::make(['status' => 'active'], ['status' => 'in_enum:'.TestEnum::class]);
    expect($validator->passes())->toBe(true);

    $validator = Validator::make(['status' => 'invalid'], ['status' => 'in_enum:'.TestEnum::class]);
    expect($validator->fails())->toBe(true);

    // Test enum_exists rule
    $validator = Validator::make(['status' => 'active'], ['status' => 'enum_exists:'.TestEnum::class]);
    expect($validator->passes())->toBe(true);

    $validator = Validator::make(['status' => 'invalid'], ['status' => 'enum_exists:'.TestEnum::class]);
    expect($validator->fails())->toBe(true);
});

it('handles validation rule with empty parameters', function () {
    $this->provider->boot();

    // Test in_enum rule with empty parameters
    $validator = Validator::make(['status' => 'active'], ['status' => 'in_enum:']);
    expect($validator->fails())->toBe(true);

    // Test enum_exists rule with empty parameters
    $validator = Validator::make(['status' => 'active'], ['status' => 'enum_exists:']);
    expect($validator->fails())->toBe(true);
});

it('provides custom error messages for validation rules', function () {
    $this->provider->boot();

    // Test in_enum error message
    $validator = Validator::make(['status' => 'invalid'], ['status' => 'in_enum:'.TestEnum::class]);
    expect($validator->fails())->toBe(true);

    $errors = $validator->errors();
    expect($errors->first('status'))->toContain('TestEnum');

    // Test enum_exists error message
    $validator = Validator::make(['status' => 'invalid'], ['status' => 'enum_exists:'.TestEnum::class]);
    expect($validator->fails())->toBe(true);

    $errors = $validator->errors();
    expect($errors->first('status'))->toContain('TestEnum');
});

it('handles validation rule replacer with no parameters', function () {
    $this->provider->boot();

    // Test in_enum replacer with no parameters
    $validator = Validator::make(['status' => 'invalid'], ['status' => 'in_enum']);
    expect($validator->fails())->toBe(true);

    $errors = $validator->errors();
    expect($errors->first('status'))->toContain('enum');

    // Test enum_exists replacer with no parameters
    $validator = Validator::make(['status' => 'invalid'], ['status' => 'enum_exists']);
    expect($validator->fails())->toBe(true);

    $errors = $validator->errors();
    expect($errors->first('status'))->toContain('enum');
});

// Test enum class for validation
enum TestEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
