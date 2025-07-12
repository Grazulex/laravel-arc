<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\DtoCollection;
use Grazulex\LaravelArc\Support\Traits\ConvertsData;
use Grazulex\LaravelArc\Support\Traits\DtoUtilities;
use Grazulex\LaravelArc\Support\Traits\ValidatesData;

final class TestUserDto
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

/**
 * Test the main DTO collection workflow
 */
it('can convert models to DTO collection like Laravel Resources', function () {
    // Create mock models (simulating Eloquent models)
    $mockModels = collect([
        (object) ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'active'],
        (object) ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'inactive'],
        (object) ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'status' => 'active'],
    ]);

    // Convert models to DTO collection (main feature)
    $dtoCollection = TestUserDto::fromModels($mockModels);

    // Verify we get a specialized DtoCollection
    expect($dtoCollection)->toBeInstanceOf(DtoCollection::class);
    expect($dtoCollection->count())->toBe(3);

    // Test API resource format (like Laravel Resources)
    $apiResource = $dtoCollection->toArrayResource();
    expect($apiResource)->toHaveKey('data');
    expect($apiResource['data'])->toHaveCount(3);
    expect($apiResource['data'][0])->toEqual([
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'status' => 'active',
    ]);

    // Test JSON resource format
    $jsonResource = $dtoCollection->toJsonResource();
    expect($jsonResource)->toBeString();
    $decoded = json_decode($jsonResource, true);
    expect($decoded)->toHaveKey('data');
    expect($decoded['data'])->toHaveCount(3);

    // Test with meta information
    $meta = ['total' => 100, 'per_page' => 10];
    $resourceWithMeta = $dtoCollection->toArrayResource($meta);
    expect($resourceWithMeta)->toHaveKey('meta');
    expect($resourceWithMeta['meta'])->toBe($meta);
});

/**
 * Test filtering and grouping like Laravel Collections
 */
it('can filter and group DTO collections', function () {
    $mockModels = collect([
        (object) ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'active'],
        (object) ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'inactive'],
        (object) ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'status' => 'active'],
        (object) ['id' => 4, 'name' => 'Alice Brown', 'email' => 'alice@example.com', 'status' => 'pending'],
    ]);

    $dtoCollection = TestUserDto::fromModels($mockModels);

    // Test filtering (like Laravel Collections)
    $activeUsers = $dtoCollection->where('status', 'active');
    expect($activeUsers->count())->toBe(2);

    // Test grouping (like Laravel Collections)
    $groupedByStatus = $dtoCollection->groupBy('status');
    expect($groupedByStatus->keys()->sort()->values()->toArray())->toEqual(['active', 'inactive', 'pending']);
    expect($groupedByStatus->get('active')->count())->toBe(2);
    expect($groupedByStatus->get('inactive')->count())->toBe(1);
    expect($groupedByStatus->get('pending')->count())->toBe(1);
});

/**
 * Test field selection and exclusion
 */
it('can select and exclude fields from DTOs', function () {
    $mockModel = (object) ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'active'];
    $dto = TestUserDto::fromModel($mockModel);

    // Test field selection
    $onlyNameAndEmail = $dto->only(['name', 'email']);
    expect($onlyNameAndEmail)->toHaveKey('name');
    expect($onlyNameAndEmail)->toHaveKey('email');
    expect($onlyNameAndEmail)->not()->toHaveKey('id');
    expect($onlyNameAndEmail)->not()->toHaveKey('status');

    // Test field exclusion
    $exceptId = $dto->except(['id']);
    expect($exceptId)->toHaveKey('name');
    expect($exceptId)->toHaveKey('email');
    expect($exceptId)->toHaveKey('status');
    expect($exceptId)->not()->toHaveKey('id');
});

/**
 * Test JSON conversion methods
 */
it('can convert DTOs to JSON like Laravel Resources', function () {
    $mockModels = collect([
        (object) ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'active'],
        (object) ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'inactive'],
    ]);

    // Test collection to JSON (like Laravel Resource Collections)
    $collectionJson = TestUserDto::collectionToJson($mockModels);
    expect($collectionJson)->toBeString();
    $decoded = json_decode($collectionJson, true);
    expect($decoded)->toHaveKey('data');
    expect($decoded['data'])->toHaveCount(2);

    // Test individual DTO to JSON
    $dto = TestUserDto::fromModel($mockModels->first());
    $individualJson = $dto->toJson();
    expect($individualJson)->toBeString();
    $decodedIndividual = json_decode($individualJson, true);
    expect($decodedIndividual)->toHaveKey('id');
    expect($decodedIndividual)->toHaveKey('name');
});
