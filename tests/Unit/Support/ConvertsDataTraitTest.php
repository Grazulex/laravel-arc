<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\DtoCollection;
use Grazulex\LaravelArc\Support\Traits\ConvertsData;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

// Test DTO class using ConvertsData trait
final class ConvertedTestDto
{
    use ConvertsData;

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
}

describe('ConvertsData Trait', function () {
    beforeEach(function () {
        $this->models = collect([
            (object) ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'active'],
            (object) ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'inactive'],
            (object) ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'status' => 'active'],
        ]);
    });

    it('converts models to DTO collection', function () {
        $dtos = ConvertedTestDto::fromModels($this->models);

        expect($dtos)->toBeInstanceOf(DtoCollection::class);
        expect($dtos->count())->toBe(3);
        expect($dtos->first())->toBeInstanceOf(ConvertedTestDto::class);
    });

    it('converts models to standard collection', function () {
        $dtos = ConvertedTestDto::fromModelsAsCollection($this->models);

        expect($dtos)->toBeInstanceOf(Collection::class);
        expect($dtos)->not->toBeInstanceOf(DtoCollection::class);
        expect($dtos->count())->toBe(3);
        expect($dtos->first())->toBeInstanceOf(ConvertedTestDto::class);
    });

    it('converts paginator to array', function () {
        $paginator = new LengthAwarePaginator(
            $this->models->take(2),
            5, // total
            2, // per page
            1, // current page
            ['path' => '/users']
        );

        $result = ConvertedTestDto::fromPaginator($paginator);

        expect($result)->toBeArray();
        expect($result)->toHaveKeys(['data', 'meta']);
        expect($result['data'])->toHaveCount(2);
        expect($result['meta'])->toHaveKeys(['current_page', 'per_page', 'total', 'last_page']);
    });

    it('converts collection to JSON', function () {
        $json = ConvertedTestDto::collectionToJson($this->models);

        expect($json)->toBeString();
        $decoded = json_decode($json, true);
        expect($decoded)->toHaveKey('data');
        expect($decoded['data'])->toHaveCount(3);
    });

    it('converts DTO to JSON', function () {
        $dto = ConvertedTestDto::fromModel($this->models->first());
        $json = $dto->toJson();

        expect($json)->toBeString();
        $decoded = json_decode($json, true);
        expect($decoded)->toEqual([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'active',
        ]);
    });

    it('converts DTO to JSON with options', function () {
        $dto = ConvertedTestDto::fromModel($this->models->first());
        $json = $dto->toJson(JSON_PRETTY_PRINT);

        expect($json)->toBeString();
        expect($json)->toContain("\n"); // Should contain newlines due to pretty print
    });

    it('converts DTO to collection', function () {
        $dto = ConvertedTestDto::fromModel($this->models->first());
        $collection = $dto->toCollection();

        expect($collection)->toBeInstanceOf(Collection::class);
        expect($collection->toArray())->toEqual([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'active',
        ]);
    });

    it('gets only specified keys', function () {
        $dto = ConvertedTestDto::fromModel($this->models->first());
        $filtered = $dto->only(['id', 'name']);

        expect($filtered)->toEqual([
            'id' => 1,
            'name' => 'John Doe',
        ]);
    });

    it('gets all keys except specified ones', function () {
        $dto = ConvertedTestDto::fromModel($this->models->first());
        $filtered = $dto->except(['id', 'status']);

        expect($filtered)->toEqual([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    });

    it('handles empty models collection', function () {
        $emptyModels = collect([]);
        $dtos = ConvertedTestDto::fromModels($emptyModels);

        expect($dtos)->toBeInstanceOf(DtoCollection::class);
        expect($dtos->count())->toBe(0);
    });

    it('handles array input for models', function () {
        $modelsArray = $this->models->toArray();
        $dtos = ConvertedTestDto::fromModels($modelsArray);

        expect($dtos)->toBeInstanceOf(DtoCollection::class);
        expect($dtos->count())->toBe(3);
    });
});
