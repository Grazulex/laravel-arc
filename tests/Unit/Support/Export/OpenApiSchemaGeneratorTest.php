<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\Export\OpenApi\OpenApiSchemaGenerator;
use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;
use Symfony\Component\Yaml\Yaml;

beforeEach(function () {
    BehavioralTraitRegistry::registerDefaults();

    $this->order = [
        'header' => [
            'dto' => 'OrderDTO',
            'namespace' => 'App\\DTO',
            'description' => 'A customer order',
            'traits' => ['HasTimestamps'],
        ],
        'fields' => [
            'id' => ['type' => 'uuid'],
            'reference' => ['type' => 'string', 'rules' => ['max:20']],
            'note' => ['type' => 'text', 'required' => false],
            'status' => ['type' => 'enum', 'values' => ['pending', 'paid']],
        ],
        'relations' => [
            'customer' => ['type' => 'belongsTo', 'dto' => 'App\\DTO\\CustomerDTO'],
            'lines' => ['type' => 'hasMany', 'dto' => 'OrderLineDTO'],
            'note' => ['type' => 'hasOne', 'dto' => 'NoteDTO'],
        ],
    ];

    $this->customer = [
        'header' => ['class' => 'CustomerDTO'],
        'fields' => ['email' => ['type' => 'email']],
    ];
});

it('generates an object schema from a definition', function () {
    $schema = (new OpenApiSchemaGenerator())->generateSchema($this->order);

    expect($schema)->toBe([
        'type' => 'object',
        'description' => 'A customer order',
        'properties' => [
            'id' => ['type' => 'string', 'format' => 'uuid'],
            'reference' => ['type' => 'string', 'maxLength' => 20],
            'note' => ['type' => ['string', 'null']],
            'status' => ['type' => 'string', 'enum' => ['pending', 'paid']],
            'created_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
            'updated_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
            'customer' => ['$ref' => '#/components/schemas/CustomerDTO'],
            'lines' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/OrderLineDTO']],
        ],
        'required' => ['id', 'reference', 'status', 'customer', 'lines'],
    ]);
});

it('resolves the schema name from the header', function () {
    $generator = new OpenApiSchemaGenerator();

    expect($generator->schemaName($this->order))->toBe('OrderDTO');
    expect($generator->schemaName(['header' => ['class' => 'App\\DTO\\UserDTO']]))->toBe('UserDTO');
    expect($generator->schemaName(['class' => 'TagDTO']))->toBe('TagDTO');
});

it('rejects a definition without a DTO name', function () {
    (new OpenApiSchemaGenerator())->schemaName(['fields' => []]);
})->throws(InvalidArgumentException::class, 'no DTO name');

it('builds sorted components', function () {
    $components = (new OpenApiSchemaGenerator())->generateComponents([$this->order, $this->customer]);

    expect(array_keys($components['schemas']))->toBe(['CustomerDTO', 'OrderDTO']);
    expect($components['schemas']['CustomerDTO'])->toBe([
        'type' => 'object',
        'properties' => ['email' => ['type' => 'string', 'format' => 'email']],
        'required' => ['email'],
    ]);
});

it('rejects duplicate schema names', function () {
    (new OpenApiSchemaGenerator())->generateComponents([$this->customer, $this->customer]);
})->throws(InvalidArgumentException::class, 'Duplicate OpenAPI schema name: CustomerDTO');

it('builds a complete document', function () {
    $document = (new OpenApiSchemaGenerator('3.0'))->generateDocument([$this->customer], [
        'title' => 'Shop API',
        'version' => '2.1.0',
        'description' => 'Shop schemas',
    ]);

    expect($document['openapi'])->toBe('3.0.3');
    expect($document['info'])->toBe(['title' => 'Shop API', 'version' => '2.1.0', 'description' => 'Shop schemas']);
    expect($document['paths'])->toBe([]);
    expect($document['components']['schemas'])->toHaveKey('CustomerDTO');
});

it('uses OpenAPI 3.1 by default', function () {
    $generator = new OpenApiSchemaGenerator();

    expect($generator->openApiVersion())->toBe('3.1.0');
    expect($generator->generateDocument([])['info'])->toBe(['title' => 'DTO Schemas', 'version' => '1.0.0']);
});

it('rejects an unsupported OpenAPI version', function () {
    new OpenApiSchemaGenerator('2.0');
})->throws(InvalidArgumentException::class, 'Unsupported OpenAPI version');

it('lists references without a schema', function () {
    $generator = new OpenApiSchemaGenerator();
    $document = $generator->generateDocument([$this->order, $this->customer]);

    expect($generator->missingReferences($document))->toBe(['OrderLineDTO']);
    expect($generator->missingReferences(['schemas' => []]))->toBe([]);
});

it('encodes empty maps as JSON objects and keeps literal lists', function () {
    $generator = new OpenApiSchemaGenerator();
    $document = $generator->generateDocument([[
        'header' => ['dto' => 'BagDTO'],
        'fields' => [
            'items' => ['type' => 'array', 'default' => []],
            'anything' => ['type' => 'mixed'],
        ],
    ], [
        'header' => ['dto' => 'EmptyDTO'],
    ]]);

    $json = $generator->toJson($document);
    $decoded = json_decode($json);

    expect($json)->toEndWith("}\n");
    expect($decoded->paths)->toBeInstanceOf(stdClass::class);
    expect($decoded->components->schemas->EmptyDTO->properties)->toBeInstanceOf(stdClass::class);
    expect($decoded->components->schemas->BagDTO->properties->items->items)->toBeInstanceOf(stdClass::class);
    expect($decoded->components->schemas->BagDTO->properties->items->default)->toBe([]);
    expect($decoded->components->schemas->BagDTO->properties->anything)->toBeInstanceOf(stdClass::class);
});

it('encodes the document as YAML', function () {
    $generator = new OpenApiSchemaGenerator();
    $yaml = $generator->toYaml($generator->generateDocument([$this->order, $this->customer]));

    $parsed = Yaml::parse($yaml);

    expect($yaml)->toMatch('/^paths: \{\s*\}$/m');
    expect($parsed['openapi'])->toBe('3.1.0');
    expect($parsed['components']['schemas']['OrderDTO']['properties']['customer'])
        ->toBe(['$ref' => '#/components/schemas/CustomerDTO']);
    expect($parsed['components']['schemas']['OrderDTO']['required'])
        ->toBe(['id', 'reference', 'status', 'customer', 'lines']);
});
