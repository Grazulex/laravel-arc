<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\Export\OpenApi\OpenApiFieldMapper;
use Tests\Fixtures\Enums\Priority;
use Tests\Fixtures\Enums\Status;

beforeEach(function () {
    $this->mapper = new OpenApiFieldMapper();
    $this->legacy = new OpenApiFieldMapper('3.0.3');
});

it('maps scalar types with their format', function (string $type, array $expected) {
    expect($this->mapper->map(['type' => $type]))->toBe($expected);
})->with([
    'string' => ['string', ['type' => 'string']],
    'text' => ['text', ['type' => 'string']],
    'id' => ['id', ['type' => 'string']],
    'uuid' => ['uuid', ['type' => 'string', 'format' => 'uuid']],
    'email' => ['email', ['type' => 'string', 'format' => 'email']],
    'url' => ['url', ['type' => 'string', 'format' => 'uri']],
    'decimal' => ['decimal', ['type' => 'string', 'format' => 'decimal']],
    'date' => ['date', ['type' => 'string', 'format' => 'date']],
    'datetime' => ['datetime', ['type' => 'string', 'format' => 'date-time']],
    'time' => ['time', ['type' => 'string', 'format' => 'time']],
    'integer' => ['integer', ['type' => 'integer']],
    'bigint' => ['bigInteger', ['type' => 'integer', 'format' => 'int64']],
    'float' => ['float', ['type' => 'number', 'format' => 'float']],
    'boolean' => ['boolean', ['type' => 'boolean']],
    'json' => ['json', ['type' => 'object']],
    'unknown' => ['somethingElse', []],
]);

it('accepts a type given as a plain string', function () {
    expect($this->mapper->map('integer'))->toBe(['type' => 'integer']);
});

it('marks nullable and optional fields as nullable', function () {
    expect($this->mapper->map(['type' => 'string', 'nullable' => true]))->toBe(['type' => ['string', 'null']]);
    expect($this->mapper->map(['type' => 'uuid', 'required' => false]))->toBe(['type' => ['string', 'null'], 'format' => 'uuid']);
    expect($this->mapper->map(['type' => 'string', 'rules' => ['nullable']]))->toBe(['type' => ['string', 'null']]);
    expect($this->mapper->map(['type' => 'mixed', 'nullable' => true]))->toBe([]);
});

it('uses the nullable keyword for OpenAPI 3.0', function () {
    expect($this->legacy->map(['type' => 'string', 'required' => false]))->toBe(['type' => 'string', 'nullable' => true]);
});

it('maps enums declared with values', function () {
    expect($this->mapper->map(['type' => 'enum', 'values' => ['draft', 'published']]))
        ->toBe(['type' => 'string', 'enum' => ['draft', 'published']]);

    expect($this->mapper->map(['type' => 'enum', 'values' => ['draft', 'published'], 'required' => false]))
        ->toBe(['type' => ['string', 'null'], 'enum' => ['draft', 'published', null]]);

    expect($this->legacy->map(['type' => 'enum', 'values' => ['draft'], 'nullable' => true]))
        ->toBe(['type' => 'string', 'enum' => ['draft', null], 'nullable' => true]);
});

it('maps enums backed by a PHP enum class', function () {
    expect($this->mapper->map(['type' => 'enum', 'class' => Status::class]))
        ->toBe(['type' => 'string', 'enum' => ['draft', 'published', 'archived']]);

    expect($this->mapper->map(['type' => 'enum', 'enum_class' => Priority::class]))
        ->toBe(['type' => 'integer', 'enum' => [1, 2, 3]]);

    expect($this->mapper->map(['type' => 'enum', 'class' => 'App\\Enums\\Missing']))
        ->toBe(['type' => 'string']);
});

it('maps the enum key of a string field', function () {
    expect($this->mapper->map(['type' => 'string', 'enum' => ['active', 'inactive']]))
        ->toBe(['type' => 'string', 'enum' => ['active', 'inactive']]);
});

it('maps nested DTOs to references', function () {
    expect($this->mapper->map(['type' => 'dto', 'dto' => 'App\\DTO\\CustomerDTO']))
        ->toBe(['$ref' => '#/components/schemas/CustomerDTO']);

    expect($this->mapper->map(['type' => 'dto', 'dto' => 'CustomerDTO', 'required' => false]))
        ->toBe(['oneOf' => [['$ref' => '#/components/schemas/CustomerDTO'], ['type' => 'null']]]);

    expect($this->legacy->map(['type' => 'dto', 'dto' => 'CustomerDTO', 'required' => false]))
        ->toBe(['allOf' => [['$ref' => '#/components/schemas/CustomerDTO']], 'nullable' => true]);

    expect($this->mapper->map(['type' => 'dto', 'dto' => 'CustomerDTO', 'description' => 'Buyer']))
        ->toBe(['allOf' => [['$ref' => '#/components/schemas/CustomerDTO']], 'description' => 'Buyer']);

    expect($this->mapper->map(['type' => 'dto']))->toBe(['type' => 'object']);
});

it('supports a custom reference prefix', function () {
    $mapper = new OpenApiFieldMapper('3.1.0', '#/definitions/');

    expect($mapper->map(['type' => 'dto', 'dto' => 'CustomerDTO']))->toBe(['$ref' => '#/definitions/CustomerDTO']);
});

it('maps arrays and their items', function () {
    expect($this->mapper->map(['type' => 'array', 'items' => ['type' => 'dto', 'dto' => 'LineDTO']]))
        ->toBe(['type' => 'array', 'items' => ['$ref' => '#/components/schemas/LineDTO']]);

    expect($this->mapper->map(['type' => 'array', 'items' => 'integer']))
        ->toBe(['type' => 'array', 'items' => ['type' => 'integer']]);

    expect($this->mapper->map(['type' => 'array']))->toBe(['type' => 'array', 'items' => []]);

    expect($this->mapper->map(['type' => 'array', 'rules' => ['distinct', 'min:1', 'max:5']]))
        ->toBe(['type' => 'array', 'items' => [], 'uniqueItems' => true, 'minItems' => 1, 'maxItems' => 5]);
});

it('maps min and max rules on strings to lengths', function () {
    expect($this->mapper->map(['type' => 'string', 'rules' => ['min:3', 'max:255', 'regex:/^[a-z]+$/']]))
        ->toBe(['type' => 'string', 'minLength' => 3, 'maxLength' => 255, 'pattern' => '^[a-z]+$']);

    expect($this->mapper->map(['type' => 'string', 'min_length' => 2, 'max_length' => 100]))
        ->toBe(['type' => 'string', 'minLength' => 2, 'maxLength' => 100]);

    expect($this->mapper->map(['type' => 'string', 'validation' => ['size:2']]))
        ->toBe(['type' => 'string', 'minLength' => 2, 'maxLength' => 2]);
});

it('maps min and max rules on numbers to bounds', function () {
    expect($this->mapper->map(['type' => 'integer', 'rules' => ['min:1', 'max:10']]))
        ->toBe(['type' => 'integer', 'minimum' => 1, 'maximum' => 10]);

    expect($this->mapper->map(['type' => 'integer', 'min' => 18, 'max' => 120]))
        ->toBe(['type' => 'integer', 'minimum' => 18, 'maximum' => 120]);

    expect($this->mapper->map(['type' => 'integer', 'rules' => ['between:1,5']]))
        ->toBe(['type' => 'integer', 'minimum' => 1, 'maximum' => 5]);

    expect($this->mapper->map(['type' => 'float', 'rules' => ['gt:0', 'lte:99.5']]))
        ->toBe(['type' => 'number', 'format' => 'float', 'exclusiveMinimum' => 0, 'maximum' => 99.5]);

    expect($this->legacy->map(['type' => 'float', 'rules' => ['gt:0', 'lt:100']]))
        ->toBe(['type' => 'number', 'format' => 'float', 'minimum' => 0, 'exclusiveMinimum' => true, 'maximum' => 100, 'exclusiveMaximum' => true]);
});

it('derives formats and enums from validation rules', function () {
    expect($this->mapper->map(['type' => 'string', 'rules' => ['email']]))
        ->toBe(['type' => 'string', 'format' => 'email']);

    expect($this->mapper->map(['type' => 'string', 'validation' => 'url|max:2048']))
        ->toBe(['type' => 'string', 'format' => 'uri', 'maxLength' => 2048]);

    expect($this->mapper->map(['type' => 'string', 'rules' => ['in:small,large']]))
        ->toBe(['type' => 'string', 'enum' => ['small', 'large']]);

    expect($this->mapper->map(['type' => 'integer', 'rules' => ['in:1,2']]))
        ->toBe(['type' => 'integer', 'enum' => [1, 2]]);

    expect($this->mapper->map(['type' => 'uuid', 'rules' => ['email']]))
        ->toBe(['type' => 'string', 'format' => 'uuid']);
});

it('ignores rules that do not apply to the type', function () {
    expect($this->mapper->map(['type' => 'boolean', 'rules' => ['min:1', 'regex:/x/', 'distinct']]))
        ->toBe(['type' => 'boolean']);
});

it('adds documentation keys', function () {
    expect($this->mapper->map([
        'type' => 'string',
        'format' => 'password',
        'description' => 'Secret',
        'default' => 'changeme',
        'example' => 's3cr3t',
    ]))->toBe([
        'type' => 'string',
        'format' => 'password',
        'description' => 'Secret',
        'default' => 'changeme',
        'example' => 's3cr3t',
    ]);

    expect($this->mapper->map(['type' => 'string', 'default' => null]))->toBe(['type' => 'string']);
});

it('maps relations', function () {
    expect($this->mapper->mapRelation(['type' => 'belongsTo', 'dto' => 'App\\DTO\\UserDTO']))
        ->toBe(['$ref' => '#/components/schemas/UserDTO']);

    expect($this->mapper->mapRelation(['type' => 'hasOne', 'dto' => 'ProfileDTO', 'required' => false]))
        ->toBe(['oneOf' => [['$ref' => '#/components/schemas/ProfileDTO'], ['type' => 'null']]]);

    expect($this->mapper->mapRelation(['type' => 'hasMany', 'dto' => 'CommentDTO']))
        ->toBe(['type' => 'array', 'items' => ['$ref' => '#/components/schemas/CommentDTO']]);

    expect($this->mapper->mapRelation(['type' => 'belongs_to_many', 'class' => 'TagDTO']))
        ->toBe(['type' => 'array', 'items' => ['$ref' => '#/components/schemas/TagDTO']]);
});

it('tells whether a field is required', function () {
    expect($this->mapper->isRequired(['type' => 'string']))->toBeTrue();
    expect($this->mapper->isRequired(['type' => 'string', 'nullable' => true]))->toBeTrue();
    expect($this->mapper->isRequired(['type' => 'string', 'required' => false]))->toBeFalse();
});

it('rejects an unsupported OpenAPI version', function () {
    new OpenApiFieldMapper('2.0');
})->throws(InvalidArgumentException::class, 'Unsupported OpenAPI version');
