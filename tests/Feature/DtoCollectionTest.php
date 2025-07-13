<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\DtoCollection;
use Grazulex\LaravelArc\Support\Traits\ConvertsData;
use Grazulex\LaravelArc\Support\Traits\DtoUtilities;
use Grazulex\LaravelArc\Support\Traits\ValidatesData;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

// Shared DTO class for all collection tests
final class TestDto
{
    use ConvertsData, DtoUtilities, ValidatesData;

    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $status = 'active'
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            status: $model->status ?? 'active'
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
        ];
    }

    public function isValid(): bool
    {
        return ! empty($this->name) &&
               ! empty($this->email) &&
               filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function getErrors(): array
    {
        $errors = [];

        if (empty($this->name)) {
            $errors['name'] = 'Name is required';
        }

        if (empty($this->email)) {
            $errors['email'] = 'Email is required';
        } elseif (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Invalid email format';
        }

        return $errors;
    }
}

// Dataset pour différents scénarios de test
dataset('collection_operations', [
    'filtering' => ['filter', 'where', ['status', 'active'], 2],
    'grouping' => ['group', 'groupBy', ['status'], ['active', 'inactive', 'pending'], ['active' => 2, 'inactive' => 1, 'pending' => 1]],
]);

dataset('resource_formats', [
    'array' => ['toArrayResource', 'array', true],
    'json' => ['toJsonResource', 'string', true],
]);

dataset('dto_field_operations', [
    'select_fields' => ['only', [['name', 'email']], ['name', 'email'], ['id', 'status']],
    'exclude_fields' => ['except', [['id']], ['name', 'email', 'status'], ['id']],
]);

// Helper function to create test data
function createTestModels(): Collection
{
    return collect([
        (object) ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'active'],
        (object) ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'inactive'],
        (object) ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'status' => 'active'],
        (object) ['id' => 4, 'name' => 'Alice Brown', 'email' => 'alice@example.com', 'status' => 'pending'],
    ]);
}

describe('DtoCollection', function () {
    beforeEach(function () {
        $this->models = createTestModels();
        $this->collection = TestDto::fromModels($this->models);
    });

    it('creates collection from models', function () {
        expect($this->collection)->toBeInstanceOf(DtoCollection::class);
        expect($this->collection->count())->toBe(4);
    });

    it('creates collection using collection() method', function () {
        $collection = TestDto::collection($this->models);
        expect($collection)->toBeInstanceOf(DtoCollection::class);
        expect($collection->count())->toBe(4);
    });

    it('collection() method is equivalent to fromModels()', function () {
        $collection1 = TestDto::collection($this->models);
        $collection2 = TestDto::fromModels($this->models);
        
        expect($collection1)->toBeInstanceOf(DtoCollection::class);
        expect($collection2)->toBeInstanceOf(DtoCollection::class);
        expect($collection1->count())->toBe($collection2->count());
    });

    it('converts to resource format', function (string $method, string $expectedType, bool $hasDataKey) {
        $result = $this->collection->{$method}();

        if ($expectedType === 'string') {
            expect($result)->toBeString();
            $result = json_decode($result, true);
        } else {
            expect($result)->toBeArray();
        }

        if ($hasDataKey) {
            expect($result)->toHaveKey('data');
            expect($result['data'])->toHaveCount(4);
        }
    })->with('resource_formats');

    it('includes meta information', function () {
        $meta = ['total' => 100, 'per_page' => 10];
        $resource = $this->collection->toArrayResource($meta);

        expect($resource)->toHaveKeys(['data', 'meta']);
        expect($resource['meta'])->toBe($meta);
    });

    it('performs collection operations', function (string $operation, string $method, array $params, $expectedCount, ?array $expectedCounts = null) {
        if ($operation === 'filter') {
            $result = $this->collection->{$method}(...$params);
            expect($result->count())->toBe($expectedCount);
        } elseif ($operation === 'group') {
            $result = $this->collection->{$method}(...$params);
            expect($result->keys()->toArray())->toEqual($expectedCount);

            foreach ($expectedCounts as $key => $count) {
                expect($result->get($key)->count())->toBe($count);
            }
        }
    })->with('collection_operations');

    it('performs field operations on DTOs', function (string $method, array $params, array $expectedKeys, array $unexpectedKeys) {
        $dto = $this->collection->first();
        $result = $dto->{$method}(...$params);

        expect($result)->toHaveKeys($expectedKeys);

        foreach ($unexpectedKeys as $key) {
            expect($result)->not->toHaveKey($key);
        }
    })->with('dto_field_operations');

    it('converts to standard collection', function () {
        $standard = TestDto::fromModelsAsCollection($this->models);

        expect($standard)->toBeInstanceOf(Collection::class);
        expect($standard)->not->toBeInstanceOf(DtoCollection::class);
        expect($standard->count())->toBe(4);
    });

    it('handles pagination', function () {
        $paginator = new LengthAwarePaginator(
            $this->models->take(3),
            10, 3, 1, ['path' => '']
        );

        $result = TestDto::fromPaginator($paginator);

        expect($result)->toBeArray();
        expect($result)->toHaveKeys(['data', 'meta']);
        expect($result['data'])->toHaveCount(3);
        expect($result['meta'])->toHaveKeys(['current_page', 'per_page', 'total']);
    });

    it('converts to JSON', function () {
        $dto = $this->collection->first();
        $json = $dto->toJson();
        $decoded = json_decode($json, true);

        expect($json)->toBeString();
        expect($decoded)->toHaveKeys(['id', 'name', 'email', 'status']);
    });

    it('validates DTOs', function () {
        $valid = $this->collection->first();
        expect($valid->isValid())->toBe(true);
        expect($valid->getErrors())->toBeEmpty();

        $invalid = new TestDto(999, '', 'invalid-email');
        expect($invalid->isValid())->toBe(false);
        expect($invalid->getErrors())->toHaveKeys(['name', 'email']);
    });

    it('supports filtering and sorting as mentioned in the issue', function () {
        // Test the exact syntax mentioned in the issue
        $collection = TestDto::collection($this->models);
        
        // Test where() filtering
        $active = $collection->where('is_active', true);
        expect($active->count())->toBe(2); // Only 2 active users in test data
        
        // Test sortBy() 
        $sorted = $collection->sortBy('name');
        expect($sorted->first()->name)->toBe('Alice Brown'); // First alphabetically
        
        // Test chaining as mentioned in the issue
        $filteredAndSorted = $collection->where('is_active', true)->sortBy('name');
        expect($filteredAndSorted->count())->toBe(2);
        expect($filteredAndSorted->first()->name)->toBe('Bob Johnson'); // First active user alphabetically
    });

    it('supports Laravel collection methods', function () {
        $names = $this->collection->map(fn ($dto) => $dto->name);
        expect($names->toArray())->toEqual(['John Doe', 'Jane Smith', 'Bob Johnson', 'Alice Brown']);

        $sorted = $this->collection->sortBy('name');
        expect($sorted->first()->name)->toBe('Alice Brown');

        $filtered = $this->collection->filter(fn ($dto) => str_contains($dto->name, 'J'));
        expect($filtered->count())->toBe(3); // John Doe, Jane Smith, Bob Johnson
    });

    it('filters by field values', function () {
        $activeUsers = $this->collection->whereField('status', 'active');
        expect($activeUsers->count())->toBe(2);
        expect($activeUsers->pluck('name')->toArray())->toEqual(['John Doe', 'Bob Johnson']);

        $inactiveUsers = $this->collection->whereField('status', 'inactive');
        expect($inactiveUsers->count())->toBe(1);
        expect($inactiveUsers->first()->name)->toBe('Jane Smith');
    });

    it('paginates collection', function () {
        $page1 = $this->collection->paginate(2, 1);
        expect($page1)->toHaveKeys(['data', 'meta']);
        expect($page1['data'])->toHaveCount(2);
        expect($page1['meta'])->toEqual([
            'current_page' => 1,
            'per_page' => 2,
            'total' => 4,
            'last_page' => 2,
            'from' => 1,
            'to' => 2,
            'has_more_pages' => true,
        ]);

        $page2 = $this->collection->paginate(2, 2);
        expect($page2['data'])->toHaveCount(2);
        expect($page2['meta']['current_page'])->toBe(2);
        expect($page2['meta']['from'])->toBe(3);
        expect($page2['meta']['to'])->toBe(4);
        expect($page2['meta']['has_more_pages'])->toBe(false);
    });

    it('groups by field', function () {
        $grouped = $this->collection->groupByField('status');

        expect($grouped)->toBeInstanceOf(Collection::class);
        expect($grouped->keys()->toArray())->toEqual(['active', 'inactive', 'pending']);

        expect($grouped->get('active'))->toBeInstanceOf(DtoCollection::class);
        expect($grouped->get('active')->count())->toBe(2);
        expect($grouped->get('inactive')->count())->toBe(1);
        expect($grouped->get('pending')->count())->toBe(1);
    });

    it('selects only specific fields', function () {
        $selected = $this->collection->onlyFields(['name', 'email']);

        expect($selected)->toBeInstanceOf(Collection::class);
        expect($selected->count())->toBe(4);

        $first = $selected->first();
        expect($first)->toHaveKeys(['name', 'email']);
        expect($first)->not->toHaveKey('id');
        expect($first)->not->toHaveKey('status');
    });

    it('excludes specific fields', function () {
        $excluded = $this->collection->exceptFields(['id', 'status']);

        expect($excluded)->toBeInstanceOf(Collection::class);
        expect($excluded->count())->toBe(4);

        $first = $excluded->first();
        expect($first)->toHaveKeys(['name', 'email']);
        expect($first)->not->toHaveKey('id');
        expect($first)->not->toHaveKey('status');
    });
});
