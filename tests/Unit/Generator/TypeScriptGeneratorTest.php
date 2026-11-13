<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\TypeScriptGenerator;
use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;

beforeEach(function () {
    BehavioralTraitRegistry::registerDefaults();

    $this->definition = [
        'header' => [
            'dto' => 'OrderDTO',
            'namespace' => 'App\\DTO',
            'traits' => ['HasTimestamps'],
        ],
        'fields' => [
            'id' => ['type' => 'uuid'],
            'total' => ['type' => 'decimal'],
            'quantity' => ['type' => 'integer'],
            'paid' => ['type' => 'boolean'],
            'note' => ['type' => 'string', 'required' => false],
            'status' => ['type' => 'enum', 'values' => ['pending', 'paid']],
            'priority' => ['type' => 'enum', 'class' => Tests\Fixtures\Enums\Status::class],
            'customer' => ['type' => 'dto', 'dto' => 'App\\DTO\\CustomerDTO'],
            'lines' => ['type' => 'array', 'items' => ['type' => 'dto', 'dto' => 'OrderLineDTO']],
            'tags' => ['type' => 'array'],
            'meta' => ['type' => 'json', 'nullable' => true],
            'shipping-code' => ['type' => 'string'],
            'location' => ['type' => 'point'],
            'custom' => ['type' => 'somethingElse'],
        ],
    ];
});

it('generates an interface from a YAML definition', function () {
    $expected = <<<'TS'
export interface OrderDTO {
  id: string;
  total: string;
  quantity: number;
  paid: boolean;
  note: string | null;
  status: 'pending' | 'paid';
  priority: 'draft' | 'published' | 'archived';
  customer: CustomerDTO;
  lines: OrderLineDTO[];
  tags: unknown[];
  meta: Record<string, unknown> | null;
  'shipping-code': string;
  location: string;
  custom: unknown;
  created_at: string | null;
  updated_at: string | null;
}

TS;

    expect((new TypeScriptGenerator())->generate($this->definition))->toBe($expected);
});

it('emits import statements for nested DTOs when requested', function () {
    $code = (new TypeScriptGenerator())->generate($this->definition, ['imports' => true]);

    expect($code)->toStartWith(
        "import type { CustomerDTO } from './CustomerDTO';\n".
        "import type { OrderLineDTO } from './OrderLineDTO';\n\n".
        'export interface OrderDTO {'
    );
});

it('can emit a type alias without export keyword', function () {
    $code = (new TypeScriptGenerator())->generate([
        'header' => ['class' => 'TagDTO'],
        'fields' => ['name' => ['type' => 'string']],
    ], ['style' => 'type', 'export' => false]);

    expect($code)->toBe("type TagDTO = {\n  name: string;\n};\n");
});

it('wraps union item types in parentheses for arrays', function () {
    $code = (new TypeScriptGenerator())->generate([
        'header' => ['dto' => 'FilterDTO'],
        'fields' => [
            'levels' => ['type' => 'array', 'items' => ['type' => 'enum', 'values' => ['low', 'high']]],
            'ids' => ['type' => 'array', 'items' => 'integer'],
        ],
    ]);

    expect($code)
        ->toContain("levels: ('low' | 'high')[];")
        ->toContain('ids: number[];');
});

it('does not import a DTO that references itself', function () {
    $code = (new TypeScriptGenerator())->generate([
        'header' => ['dto' => 'CategoryDTO'],
        'fields' => ['parent' => ['type' => 'dto', 'dto' => 'CategoryDTO', 'required' => false]],
    ], ['imports' => true]);

    expect($code)
        ->not->toContain('import type')
        ->toContain('parent: CategoryDTO | null;');
});

it('ignores unknown behavioral traits', function () {
    $code = (new TypeScriptGenerator())->generate([
        'header' => ['dto' => 'PlainDTO', 'traits' => ['UnknownTrait']],
        'fields' => ['name' => ['type' => 'string']],
    ]);

    expect($code)->toBe("export interface PlainDTO {\n  name: string;\n}\n");
});

it('rejects a definition without a DTO name', function () {
    (new TypeScriptGenerator())->generate(['fields' => ['name' => ['type' => 'string']]]);
})->throws(InvalidArgumentException::class, 'no DTO name');

it('rejects an unsupported declaration style', function () {
    (new TypeScriptGenerator())->generate(['header' => ['dto' => 'X']], ['style' => 'class']);
})->throws(InvalidArgumentException::class, 'Unsupported TypeScript declaration style');
