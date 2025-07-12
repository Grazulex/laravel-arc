<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\DTOCollection;
use Grazulex\LaravelArc\Support\Traits\ConvertsData;

describe('DTOCollection Native Support', function () {
    it('can create a DTOCollection from models using collection() method', function () {
        // Create a mock DTO class with the ConvertsData trait
        $dtoClass = new class
        {
            use ConvertsData;

            public function __construct(
                public readonly string $name,
                public readonly string $email,
                public readonly bool $is_active,
            ) {}

            public static function fromModel($model): self
            {
                return new self(
                    $model->name,
                    $model->email,
                    $model->is_active ?? true
                );
            }

            public function toArray(): array
            {
                return [
                    'name' => $this->name,
                    'email' => $this->email,
                    'is_active' => $this->is_active,
                ];
            }
        };

        // Create mock models
        $models = [
            (object) ['name' => 'John Doe', 'email' => 'john@example.com', 'is_active' => true],
            (object) ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'is_active' => false],
            (object) ['name' => 'Bob Johnson', 'email' => 'bob@example.com', 'is_active' => true],
        ];

        // Test collection() method
        $dtoCollection = $dtoClass::collection($models);

        expect($dtoCollection)->toBeInstanceOf(DTOCollection::class);
        expect($dtoCollection)->toHaveCount(3);
        expect($dtoCollection->first()->name)->toBe('John Doe');
    });

    it('can filter DTOs using where() method', function () {
        // Create a mock DTO class
        $dtoClass = new class
        {
            use ConvertsData;

            public function __construct(
                public readonly string $name,
                public readonly bool $is_active,
            ) {}

            public static function fromModel($model): self
            {
                return new self($model->name, $model->is_active);
            }

            public function toArray(): array
            {
                return [
                    'name' => $this->name,
                    'is_active' => $this->is_active,
                ];
            }
        };

        // Create mock models
        $models = [
            (object) ['name' => 'John', 'is_active' => true],
            (object) ['name' => 'Jane', 'is_active' => false],
            (object) ['name' => 'Bob', 'is_active' => true],
        ];

        // Test where() filtering
        $dtoCollection = $dtoClass::collection($models);
        $activeUsers = $dtoCollection->where('is_active', true);

        expect($activeUsers)->toHaveCount(2);
        expect($activeUsers->pluck('name')->toArray())->toBe(['John', 'Bob']);
    });

    it('can sort DTOs using sortBy() method', function () {
        // Create a mock DTO class
        $dtoClass = new class
        {
            use ConvertsData;

            public function __construct(
                public readonly string $name,
                public readonly int $age,
            ) {}

            public static function fromModel($model): self
            {
                return new self($model->name, $model->age);
            }

            public function toArray(): array
            {
                return [
                    'name' => $this->name,
                    'age' => $this->age,
                ];
            }
        };

        // Create mock models
        $models = [
            (object) ['name' => 'John', 'age' => 30],
            (object) ['name' => 'Alice', 'age' => 25],
            (object) ['name' => 'Bob', 'age' => 35],
        ];

        // Test sortBy() method
        $dtoCollection = $dtoClass::collection($models);
        $sortedByName = $dtoCollection->sortBy('name');

        expect($sortedByName->pluck('name')->toArray())->toBe(['Alice', 'Bob', 'John']);
    });

    it('can chain where() and sortBy() methods', function () {
        // Create a mock DTO class
        $dtoClass = new class
        {
            use ConvertsData;

            public function __construct(
                public readonly string $name,
                public readonly bool $is_active,
                public readonly int $age,
            ) {}

            public static function fromModel($model): self
            {
                return new self($model->name, $model->is_active, $model->age);
            }

            public function toArray(): array
            {
                return [
                    'name' => $this->name,
                    'is_active' => $this->is_active,
                    'age' => $this->age,
                ];
            }
        };

        // Create mock models
        $models = [
            (object) ['name' => 'John', 'is_active' => true, 'age' => 30],
            (object) ['name' => 'Alice', 'is_active' => true, 'age' => 25],
            (object) ['name' => 'Bob', 'is_active' => false, 'age' => 35],
            (object) ['name' => 'Charlie', 'is_active' => true, 'age' => 28],
        ];

        // Test chaining where() and sortBy() - this is the key feature from the issue
        $dtoCollection = $dtoClass::collection($models);
        $result = $dtoCollection
            ->where('is_active', true)
            ->sortBy('name');

        expect($result)->toHaveCount(3);
        expect($result->pluck('name')->toArray())->toBe(['Alice', 'Charlie', 'John']);
    });

    it('can use fromModels() method and get DTOCollection', function () {
        // Create a mock DTO class
        $dtoClass = new class
        {
            use ConvertsData;

            public function __construct(
                public readonly string $name,
            ) {}

            public static function fromModel($model): self
            {
                return new self($model->name);
            }

            public function toArray(): array
            {
                return ['name' => $this->name];
            }
        };

        // Create mock models
        $models = [
            (object) ['name' => 'John'],
            (object) ['name' => 'Jane'],
        ];

        // Test fromModels() method returns DTOCollection
        $dtoCollection = $dtoClass::fromModels($models);

        expect($dtoCollection)->toBeInstanceOf(DTOCollection::class);
        expect($dtoCollection)->toHaveCount(2);
    });

    it('can use advanced filtering methods on DTOCollection', function () {
        // Create a mock DTO class
        $dtoClass = new class
        {
            use ConvertsData;

            public function __construct(
                public readonly string $name,
                public readonly ?string $email,
                public readonly string $status,
            ) {}

            public static function fromModel($model): self
            {
                return new self($model->name, $model->email, $model->status);
            }

            public function toArray(): array
            {
                return [
                    'name' => $this->name,
                    'email' => $this->email,
                    'status' => $this->status,
                ];
            }
        };

        // Create mock models
        $models = [
            (object) ['name' => 'John', 'email' => 'john@example.com', 'status' => 'active'],
            (object) ['name' => 'Jane', 'email' => null, 'status' => 'inactive'],
            (object) ['name' => 'Bob', 'email' => 'bob@example.com', 'status' => 'pending'],
            (object) ['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active'],
        ];

        $dtoCollection = $dtoClass::collection($models);

        // Test whereNot()
        $notActive = $dtoCollection->whereNot('status', 'active');
        expect($notActive)->toHaveCount(2);

        // Test whereNull()
        $nullEmail = $dtoCollection->whereNull('email');
        expect($nullEmail)->toHaveCount(1);
        expect($nullEmail->first()->name)->toBe('Jane');

        // Test whereNotNull()
        $notNullEmail = $dtoCollection->whereNotNull('email');
        expect($notNullEmail)->toHaveCount(3);

        // Test whereIn()
        $activeOrPending = $dtoCollection->whereIn('status', ['active', 'pending']);
        expect($activeOrPending)->toHaveCount(3);

        // Test whereNotIn()
        $notInactive = $dtoCollection->whereNotIn('status', ['inactive']);
        expect($notInactive)->toHaveCount(3);

        // Test sortByDesc()
        $sortedDesc = $dtoCollection->sortByDesc('name');
        expect($sortedDesc->first()->name)->toBe('John');
    });
});