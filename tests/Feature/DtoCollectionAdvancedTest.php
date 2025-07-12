<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\DtoCollection;
use Grazulex\LaravelArc\Support\Traits\ConvertsData;
use Grazulex\LaravelArc\Support\Traits\DtoUtilities;
use Grazulex\LaravelArc\Support\Traits\ValidatesData;
use Illuminate\Pagination\LengthAwarePaginator as ConcretePaginator;
use Illuminate\Support\Collection;

/**
 * Integration test for DTO collection functionality
 */
it('can handle complete DTO collection workflow', function () {
    // Create a sample DTO class
    $dtoClass = new class(1, 'Test', 'test@example.com', 'active')
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

        public static function fromArray(array $data): self
        {
            return new self(
                id: $data['id'],
                name: $data['name'],
                email: $data['email'],
                status: $data['status'] ?? 'active'
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
    }(1, 'temp', 'temp@example.com');

    // Create mock models
    $mockModels = collect([
        (object) ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'active'],
        (object) ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'inactive'],
        (object) ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'status' => 'active'],
        (object) ['id' => 4, 'name' => 'Alice Brown', 'email' => 'alice@example.com', 'status' => 'pending'],
    ]);

    // Test 1: Basic DTO collection creation
    $dtoCollection = $dtoClass::fromModels($mockModels);
    expect($dtoCollection)->toBeInstanceOf(DtoCollection::class);
    expect($dtoCollection->count())->toBe(4);

    // Test 2: Convert to array resource format
    $arrayResource = $dtoCollection->toArrayResource();
    expect($arrayResource)->toHaveKey('data');
    expect($arrayResource['data'])->toHaveCount(4);
    expect($arrayResource['data'][0])->toHaveKey('id');
    expect($arrayResource['data'][0])->toHaveKey('name');
    expect($arrayResource['data'][0])->toHaveKey('email');
    expect($arrayResource['data'][0])->toHaveKey('status');

    // Test 3: Convert to JSON resource format
    $jsonResource = $dtoCollection->toJsonResource();
    expect($jsonResource)->toBeString();
    $decodedJson = json_decode($jsonResource, true);
    expect($decodedJson)->toHaveKey('data');
    expect($decodedJson['data'])->toHaveCount(4);

    // Test 4: Test with meta information
    $metaInfo = ['total' => 100, 'per_page' => 10];
    $resourceWithMeta = $dtoCollection->toArrayResource($metaInfo);
    expect($resourceWithMeta)->toHaveKey('meta');
    expect($resourceWithMeta['meta'])->toBe($metaInfo);

    // Test 5: Test filtering
    $activeUsers = $dtoCollection->where('status', 'active');
    expect($activeUsers->count())->toBe(2);

    // Test 6: Test grouping
    $groupedByStatus = $dtoCollection->groupBy('status');
    expect($groupedByStatus->keys()->toArray())->toContain('active', 'inactive', 'pending');
    expect($groupedByStatus->get('active')->count())->toBe(2);
    expect($groupedByStatus->get('inactive')->count())->toBe(1);
    expect($groupedByStatus->get('pending')->count())->toBe(1);

    // Test 7: Test field selection
    $firstDto = $dtoCollection->first();
    $onlyNameAndEmail = $firstDto->only(['name', 'email']);
    expect($onlyNameAndEmail)->toHaveKey('name');
    expect($onlyNameAndEmail)->toHaveKey('email');
    expect($onlyNameAndEmail)->not()->toHaveKey('id');
    expect($onlyNameAndEmail)->not()->toHaveKey('status');

    // Test 8: Test field exclusion
    $exceptId = $firstDto->except(['id']);
    expect($exceptId)->toHaveKey('name');
    expect($exceptId)->toHaveKey('email');
    expect($exceptId)->toHaveKey('status');
    expect($exceptId)->not()->toHaveKey('id');

    // Test 9: Test standard collection conversion
    $standardCollection = $dtoClass::fromModelsAsCollection($mockModels);
    expect($standardCollection)->toBeInstanceOf(Collection::class);
    expect($standardCollection)->not()->toBeInstanceOf(DtoCollection::class);
    expect($standardCollection->count())->toBe(4);

    // Test 10: Test JSON conversion
    $collectionJson = $dtoClass::collectionToJson($mockModels);
    expect($collectionJson)->toBeString();
    $decodedCollectionJson = json_decode($collectionJson, true);
    expect($decodedCollectionJson)->toHaveKey('data');
    expect($decodedCollectionJson['data'])->toHaveCount(4);

    // Test 11: Test individual DTO JSON conversion
    $individualJson = $firstDto->toJson();
    expect($individualJson)->toBeString();
    $decodedIndividualJson = json_decode($individualJson, true);
    expect($decodedIndividualJson)->toHaveKey('id');
    expect($decodedIndividualJson)->toHaveKey('name');

    // Test 12: Test DTO to collection conversion
    $dtoAsCollection = $firstDto->toCollection();
    expect($dtoAsCollection)->toBeInstanceOf(Collection::class);
    expect($dtoAsCollection->has('id'))->toBe(true);
    expect($dtoAsCollection->has('name'))->toBe(true);

    // Test 13: Test validation
    expect($firstDto->isValid())->toBe(true);
    expect($firstDto->getErrors())->toBeEmpty();

    // Test 14: Test invalid DTO
    $invalidDto = new ($dtoClass::class)(
        id: 999,
        name: '',
        email: 'invalid-email',
        status: 'active'
    );
    expect($invalidDto->isValid())->toBe(false);
    expect($invalidDto->getErrors())->not()->toBeEmpty();
    expect($invalidDto->getErrors())->toHaveKey('name');
    expect($invalidDto->getErrors())->toHaveKey('email');
});

/**
 * Test pagination functionality
 */
it('can handle paginated DTO collections', function () {
    // Create a sample DTO class
    $dtoClass = new class(1, 'Test')
    {
        use ConvertsData, DtoUtilities, ValidatesData;

        public function __construct(
            public readonly int $id,
            public readonly string $name
        ) {}

        public static function fromModel($model): self
        {
            return new self(
                id: $model->id,
                name: $model->name
            );
        }

        public function toArray(): array
        {
            return [
                'id' => $this->id,
                'name' => $this->name,
            ];
        }

        public function isValid(): bool
        {
            return ! empty($this->name);
        }

        public function getErrors(): array
        {
            return empty($this->name) ? ['name' => 'Name is required'] : [];
        }
    };

    // Create mock paginated data
    $mockData = collect([
        (object) ['id' => 1, 'name' => 'User 1'],
        (object) ['id' => 2, 'name' => 'User 2'],
        (object) ['id' => 3, 'name' => 'User 3'],
    ]);

    // Create a mock paginator
    $mockPaginator = new ConcretePaginator(
        $mockData,
        10, // total
        3,  // per page
        1,  // current page
        1   // path
    );

    // Test paginated conversion
    $paginatedResult = $dtoClass::fromPaginator($mockPaginator);

    expect($paginatedResult)->toBeArray();
    expect($paginatedResult)->toHaveKey('data');
    expect($paginatedResult)->toHaveKey('meta');

    expect($paginatedResult['data'])->toHaveCount(3);
    expect($paginatedResult['meta'])->toHaveKey('current_page');
    expect($paginatedResult['meta'])->toHaveKey('per_page');
    expect($paginatedResult['meta'])->toHaveKey('total');
    expect($paginatedResult['meta'])->toHaveKey('last_page');
    expect($paginatedResult['meta'])->toHaveKey('has_more_pages');

    expect($paginatedResult['meta']['current_page'])->toBe(1);
    expect($paginatedResult['meta']['per_page'])->toBe(3);
    expect($paginatedResult['meta']['total'])->toBe(10);
});

/**
 * Test advanced DtoCollection methods
 */
it('can use advanced DtoCollection methods', function () {
    // Create a sample DTO class
    $dtoClass = new class(1, 'Test', 'tech', 100)
    {
        use ConvertsData, DtoUtilities, ValidatesData;

        public function __construct(
            public readonly int $id,
            public readonly string $name,
            public readonly string $category,
            public readonly int $score
        ) {}

        public static function fromModel($model): self
        {
            return new self(
                id: $model->id,
                name: $model->name,
                category: $model->category,
                score: $model->score
            );
        }

        public function toArray(): array
        {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'category' => $this->category,
                'score' => $this->score,
            ];
        }

        public function isValid(): bool
        {
            return ! empty($this->name) && $this->score >= 0;
        }

        public function getErrors(): array
        {
            return [];
        }
    };

    // Create mock models with different categories and scores
    $mockModels = collect([
        (object) ['id' => 1, 'name' => 'Item A', 'category' => 'tech', 'score' => 95],
        (object) ['id' => 2, 'name' => 'Item B', 'category' => 'tech', 'score' => 87],
        (object) ['id' => 3, 'name' => 'Item C', 'category' => 'business', 'score' => 92],
        (object) ['id' => 4, 'name' => 'Item D', 'category' => 'business', 'score' => 78],
        (object) ['id' => 5, 'name' => 'Item E', 'category' => 'tech', 'score' => 89],
    ]);

    $dtoCollection = $dtoClass::fromModels($mockModels);

    // Test sorting
    $sortedByScore = $dtoCollection->sortByDesc('score');
    expect($sortedByScore->first()->score)->toBe(95);
    expect($sortedByScore->last()->score)->toBe(78);

    // Test filtering by score
    $highScores = $dtoCollection->filter(fn ($dto) => $dto->score >= 90);
    expect($highScores->count())->toBe(3);

    // Test mapping with transformation
    $names = $dtoCollection->map(fn ($dto) => $dto->name);
    expect($names->toArray())->toEqual(['Item A', 'Item B', 'Item C', 'Item D', 'Item E']);

    // Test sum and average
    $totalScore = $dtoCollection->sum('score');
    expect($totalScore)->toBe(441);

    $averageScore = $dtoCollection->avg('score');
    expect($averageScore)->toBe(88.2);

    // Test pluck
    $categories = $dtoCollection->pluck('category');
    expect($categories->unique()->toArray())->toEqual(['tech', 'business']);

    // Test complex filtering and grouping
    $techItems = $dtoCollection->where('category', 'tech');
    expect($techItems->count())->toBe(3);

    $groupedByCategory = $dtoCollection->groupBy('category');
    expect($groupedByCategory->keys()->toArray())->toEqual(['tech', 'business']);
    expect($groupedByCategory->get('tech')->count())->toBe(3);
    expect($groupedByCategory->get('business')->count())->toBe(2);
});
