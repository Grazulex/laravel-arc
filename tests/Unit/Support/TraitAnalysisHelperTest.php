<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\Traits\TraitAnalysisHelper;
use Illuminate\Database\Eloquent\Model;

it('can be instantiated with required properties', function () {
    $helper = new TraitAnalysisHelper('test-id', 'test-name');

    expect($helper->id)->toBe('test-id');
    expect($helper->name)->toBe('test-name');
});

it('can create instance from model', function () {
    $model = new class extends Model
    {
        public $id = 'model-id';

        public $name = 'model-name';
    };

    $helper = TraitAnalysisHelper::fromModel($model);

    expect($helper->id)->toBe('model-id');
    expect($helper->name)->toBe('model-name');
});

it('provides validation rules', function () {
    $rules = TraitAnalysisHelper::rules();

    expect($rules)->toBe([
        'id' => ['required', 'string'],
        'name' => ['required', 'string'],
    ]);
});

it('converts to array', function () {
    $helper = new TraitAnalysisHelper('test-id', 'test-name');

    $array = $helper->toArray();

    expect($array)->toBe([
        'id' => 'test-id',
        'name' => 'test-name',
    ]);
});

it('can use trait methods', function () {
    $helper = new TraitAnalysisHelper('test-id', 'test-name');

    // Test ConvertsData trait
    expect($helper->only(['id']))->toBe(['id' => 'test-id']);
    expect($helper->except(['id']))->toBe(['name' => 'test-name']);

    // Test DtoUtilities trait
    expect($helper->getProperty('id'))->toBe('test-id');
    expect($helper->hasProperty('name'))->toBe(true);
    expect($helper->hasProperty('nonexistent'))->toBe(false);

    // Test ValidatesData trait
    $validationResult = $helper->validate(['id' => 'test-id', 'name' => 'test-name']);
    expect($validationResult)->toBeArray();
});

it('can be compared for equality', function () {
    $helper1 = new TraitAnalysisHelper('test-id', 'test-name');
    $helper2 = new TraitAnalysisHelper('test-id', 'test-name');
    $helper3 = new TraitAnalysisHelper('different-id', 'test-name');

    expect($helper1->equals($helper2))->toBe(true);
    expect($helper1->equals($helper3))->toBe(false);
});

it('can create modified instances', function () {
    $helper = new TraitAnalysisHelper('test-id', 'test-name');

    $modified = $helper->with(['id' => 'new-id']);

    expect($modified->id)->toBe('new-id');
    expect($modified->name)->toBe('test-name');
    expect($helper->id)->toBe('test-id'); // Original unchanged
});
