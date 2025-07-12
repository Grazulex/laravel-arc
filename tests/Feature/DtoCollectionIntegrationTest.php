<?php

declare(strict_types=1);

namespace Tests\Feature;

use Grazulex\LaravelArc\Support\DtoCollection;
use Mockery;
use Tests\TestCase;

final class DtoCollectionIntegrationTest extends TestCase {}

it('can create specialized DTO collections from models', function () {
    // Create a mock DTO with traits
    $dtoClass = new class('1', 'Test', 'test@example.com')
    {
        use \Grazulex\LaravelArc\Support\Traits\ConvertsData;
        use \Grazulex\LaravelArc\Support\Traits\DtoUtilities;

        public function __construct(
            public readonly string $id,
            public readonly string $name,
            public readonly string $email,
        ) {}

        public static function fromModel($model): self
        {
            return new self(
                id: $model->id,
                name: $model->name,
                email: $model->email,
            );
        }

        public function toArray(): array
        {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
            ];
        }
    };

    // Create mock models
    $models = collect([
        (object) ['id' => '1', 'name' => 'John', 'email' => 'john@example.com'],
        (object) ['id' => '2', 'name' => 'Jane', 'email' => 'jane@example.com'],
        (object) ['id' => '3', 'name' => 'Bob', 'email' => 'bob@example.com'],
    ]);

    // Test fromModels returns DtoCollection
    $dtoCollection = $dtoClass::fromModels($models);
    expect($dtoCollection)->toBeInstanceOf(DtoCollection::class);
    expect($dtoCollection)->toHaveCount(3);

    // Test toJsonResource
    $json = $dtoCollection->toJsonResource(['total' => 3]);
    $decoded = json_decode($json, true);

    expect($decoded)->toHaveKey('data');
    expect($decoded)->toHaveKey('meta');
    expect($decoded['data'])->toHaveCount(3);
    expect($decoded['meta']['total'])->toBe(3);

    // Test whereField
    $filtered = $dtoCollection->whereField('name', 'John');
    expect($filtered)->toHaveCount(1);
    expect($filtered->first()->name)->toBe('John');

    // Test onlyFields
    $onlyNames = $dtoCollection->onlyFields(['name', 'email']);
    expect($onlyNames->first())->toHaveKey('name');
    expect($onlyNames->first())->toHaveKey('email');
    expect($onlyNames->first())->not->toHaveKey('id');

    // Test pagination
    $paginated = $dtoCollection->paginate(2, 1);
    expect($paginated)->toHaveKey('data');
    expect($paginated)->toHaveKey('meta');
    expect($paginated['data'])->toHaveCount(2);
    expect($paginated['meta']['current_page'])->toBe(1);
    expect($paginated['meta']['total'])->toBe(3);
    expect($paginated['meta']['has_more_pages'])->toBeTrue();
});

it('can handle paginated models with fromPaginator', function () {
    // Create a mock DTO class
    $dtoClass = new class('1', 'Test')
    {
        use \Grazulex\LaravelArc\Support\Traits\ConvertsData;

        public function __construct(
            public readonly string $id,
            public readonly string $name,
        ) {}

        public static function fromModel($model): self
        {
            return new self(
                id: $model->id,
                name: $model->name,
            );
        }

        public function toArray(): array
        {
            return [
                'id' => $this->id,
                'name' => $this->name,
            ];
        }
    };

    // Create a mock paginator
    $mockPaginator = Mockery::mock(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
    $mockPaginator->shouldReceive('items')->andReturn([
        (object) ['id' => '1', 'name' => 'John'],
        (object) ['id' => '2', 'name' => 'Jane'],
    ]);
    $mockPaginator->shouldReceive('currentPage')->andReturn(1);
    $mockPaginator->shouldReceive('perPage')->andReturn(2);
    $mockPaginator->shouldReceive('hasMorePages')->andReturn(true);
    $mockPaginator->shouldReceive('total')->andReturn(5);
    $mockPaginator->shouldReceive('lastPage')->andReturn(3);
    $mockPaginator->shouldReceive('firstItem')->andReturn(1);
    $mockPaginator->shouldReceive('lastItem')->andReturn(2);

    $result = $dtoClass::fromPaginator($mockPaginator);

    expect($result)->toHaveKey('data');
    expect($result)->toHaveKey('meta');
    expect($result['data'])->toHaveCount(2);
    expect($result['meta']['current_page'])->toBe(1);
    expect($result['meta']['total'])->toBe(5);
    expect($result['meta']['has_more_pages'])->toBeTrue();
});

it('can convert collections to JSON resources format', function () {
    $dtoClass = new class('Test')
    {
        use \Grazulex\LaravelArc\Support\Traits\ConvertsData;

        public function __construct(
            public readonly string $name,
        ) {}

        public static function fromModel($model): self
        {
            return new self(name: $model->name);
        }

        public function toArray(): array
        {
            return ['name' => $this->name];
        }
    };

    $models = [
        (object) ['name' => 'Test 1'],
        (object) ['name' => 'Test 2'],
    ];

    $json = $dtoClass::collectionToJson($models);
    $decoded = json_decode($json, true);

    expect($decoded)->toHaveKey('data');
    expect($decoded['data'])->toHaveCount(2);
    expect($decoded['data'][0]['name'])->toBe('Test 1');
});
