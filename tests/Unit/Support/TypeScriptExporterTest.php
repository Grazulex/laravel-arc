<?php

declare(strict_types=1);

use Carbon\Carbon;
use Grazulex\LaravelArc\Support\Export\ExporterFactory;
use Grazulex\LaravelArc\Support\Export\Exporters\TypeScriptExporter;
use Tests\Fixtures\Enums\Status;

beforeEach(function () {
    ExporterFactory::reset();
});

it('is registered in the default exporter manager', function () {
    $manager = ExporterFactory::getInstance();

    expect($manager->hasExporter('typescript'))->toBeTrue();
    expect($manager->hasCollectionExporter('typescript'))->toBeTrue();
    expect($manager->getExporter('typescript'))->toBeInstanceOf(TypeScriptExporter::class);
});

it('infers an interface from exported DTO data', function () {
    $exporter = new TypeScriptExporter();

    $result = $exporter->export([
        'id' => 1,
        'name' => 'John',
        'score' => 9.5,
        'active' => true,
        'deleted_at' => null,
        'created_at' => Carbon::parse('2026-01-01'),
        'status' => Status::DRAFT,
        'tags' => ['a', 'b'],
        'mixed' => [1, 'two'],
        'empty' => [],
        'address' => ['city' => 'Brussels', 'zip' => 1000],
    ], ['name' => 'UserDto']);

    $expected = <<<'TS'
export interface UserDto {
  id: number;
  name: string;
  score: number;
  active: boolean;
  deleted_at: null;
  created_at: string;
  status: string;
  tags: string[];
  mixed: (number | string)[];
  empty: unknown[];
  address: {
    city: string;
    zip: number;
  };
}

TS;

    expect($result)->toBe($expected);
    expect($exporter->getFormat())->toBe('typescript');
});

it('can emit a type alias', function () {
    $result = (new TypeScriptExporter())->export(['id' => 1], ['name' => 'Item', 'style' => 'type', 'export' => false]);

    expect($result)->toBe("type Item = {\n  id: number;\n};\n");
});

it('quotes keys that are not valid identifiers', function () {
    $result = (new TypeScriptExporter())->export(['first-name' => 'John']);

    expect($result)->toContain("'first-name': string;");
});

it('merges collection items and marks missing keys optional', function () {
    $result = (new TypeScriptExporter())->exportCollection([
        ['id' => 1, 'email' => null],
        ['id' => 2, 'email' => 'jane@example.com', 'nickname' => 'Jane'],
    ], ['name' => 'UserDto']);

    $expected = <<<'TS'
export interface UserDto {
  id: number;
  email: string | null;
  nickname?: string;
}

export type UserDtoCollection = {
  data: UserDto[];
};

TS;

    expect($result)->toBe($expected);
});

it('can export a collection type without the data wrapper', function () {
    $result = (new TypeScriptExporter())->exportCollection([['id' => 1]], ['name' => 'Row', 'wrap_in_data' => false]);

    expect($result)->toContain('export type RowCollection = Row[];');
});

it('rejects an invalid type name', function () {
    (new TypeScriptExporter())->export(['id' => 1], ['name' => 'not valid']);
})->throws(InvalidArgumentException::class, 'valid identifier');

it('rejects an unsupported declaration style', function () {
    (new TypeScriptExporter())->export(['id' => 1], ['style' => 'class']);
})->throws(InvalidArgumentException::class, 'Unsupported TypeScript declaration style');
