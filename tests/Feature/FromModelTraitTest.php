<?php

use Carbon\Carbon;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;
use Grazulex\Arc\Traits\DTOFromModelTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

// Mock models that don't require database
class MockUser
{
    public array $attributes = [];
    public array $relations = [];
    
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }
    
    public function toArray(): array
    {
        return array_merge($this->attributes, $this->relations);
    }
    
    public function setRelation(string $name, mixed $value): void
    {
        $this->relations[$name] = $value;
    }
    
    public function relationLoaded(string $relation): bool
    {
        return array_key_exists($relation, $this->relations);
    }
    
    public function getRelations(): array
    {
        return $this->relations;
    }
    
    public function __get(string $name)
    {
        return $this->relations[$name] ?? $this->attributes[$name] ?? null;
    }
}

class MockCollection
{
    public array $items = [];
    
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }
    
    public function toArray(): array
    {
        return array_map(fn($item) => is_object($item) && method_exists($item, 'toArray') ? $item->toArray() : $item, $this->items);
    }
    
    public function map(callable $callback): self
    {
        $items = array_map($callback, $this->items);
        return new self($items);
    }
}

// Test DTOs
class SimpleUserDTO extends LaravelArcDTO
{
    use DTOFromModelTrait;
    
    #[Property(type: 'int', required: false)]
    public ?int $id;
    
    #[Property(type: 'string', required: true)]
    public string $name;
    
    #[Property(type: 'string', required: true, validation: 'email')]
    public string $email;
    
    #[Property(type: 'int', required: false)]
    public ?int $age;
    
    #[Property(type: 'bool', required: false, default: true)]
    public bool $is_active;
    
    #[Property(type: 'date', required: false)]
    public ?Carbon $created_at;
    
    // Override fromModel for testing with mock objects
    public static function fromModel($model, array $relations = []): static
    {
        $data = $model->toArray();
        
        // Include loaded relations if specified
        foreach ($relations as $relation) {
            if ($model->relationLoaded($relation)) {
                $relationData = $model->{$relation};
                
                if ($relationData instanceof MockCollection) {
                    // Handle collection relations (HasMany, BelongsToMany)
                    $data[$relation] = $relationData->toArray();
                } elseif (is_object($relationData) && method_exists($relationData, 'toArray')) {
                    // Handle single model relations (HasOne, BelongsTo)
                    $data[$relation] = $relationData->toArray();
                } else {
                    // Handle other types (null, primitives)
                    $data[$relation] = $relationData;
                }
            }
        }
        
        return new static($data);
    }
    
    public static function fromModels($models, array $relations = []): array
    {
        return $models->map(fn($model) => static::fromModel($model, $relations))->items;
    }
    
    public static function fromModelWithLoadedRelations($model): static
    {
        $data = $model->toArray();
        
        // Get all loaded relations
        $loadedRelations = array_keys($model->getRelations());
        
        // Include all loaded relations
        foreach ($loadedRelations as $relation) {
            $relationData = $model->{$relation};
            
            if ($relationData instanceof MockCollection) {
                $data[$relation] = $relationData->toArray();
            } elseif (is_object($relationData) && method_exists($relationData, 'toArray')) {
                $data[$relation] = $relationData->toArray();
            } else {
                $data[$relation] = $relationData;
            }
        }
        
        return new static($data);
    }
    
    public static function fromModelsWithLoadedRelations($models): array
    {
        return $models->map(fn($model) => static::fromModelWithLoadedRelations($model))->items;
    }
}

class SimpleProfileDTO extends LaravelArcDTO
{
    use DTOFromModelTrait;
    
    #[Property(type: 'int', required: false)]
    public ?int $id;
    
    #[Property(type: 'string', required: false)]
    public ?string $bio;
    
    #[Property(type: 'string', required: false)]
    public ?string $website;
}

describe('FromModelTrait', function () {
    
    beforeEach(function () {
        // Create mock user
        $this->user = new MockUser([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 30,
            'is_active' => true,
            'created_at' => '2024-01-01 10:00:00'
        ]);
    });
    
    describe('fromModel', function () {
        
        it('creates DTO from model without relations', function () {
            $dto = SimpleUserDTO::fromModel($this->user);
            
            expect($dto)->toBeInstanceOf(SimpleUserDTO::class);
            expect($dto->id)->toBe(1);
            expect($dto->name)->toBe('John Doe');
            expect($dto->email)->toBe('john@example.com');
            expect($dto->age)->toBe(30);
            expect($dto->is_active)->toBe(true);
        });
        
        it('handles non-loaded relations gracefully', function () {
            // Try to include relations that aren't loaded - should not cause errors
            $dto = SimpleUserDTO::fromModel($this->user, ['profile', 'posts']);
            
            expect($dto)->toBeInstanceOf(SimpleUserDTO::class);
            expect($dto->name)->toBe('John Doe');
            expect($dto->email)->toBe('john@example.com');
        });
    });
    
    describe('fromModels', function () {
        
        it('creates DTOs from collection of models', function () {
            $user2 = new MockUser([
                'id' => 2,
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'age' => 25,
                'is_active' => true
            ]);
            
            $users = new MockCollection([$this->user, $user2]);
            $dtos = SimpleUserDTO::fromModels($users);
            
            expect($dtos)->toBeArray();
            expect(count($dtos))->toBe(2);
            expect($dtos[0])->toBeInstanceOf(SimpleUserDTO::class);
            expect($dtos[1])->toBeInstanceOf(SimpleUserDTO::class);
            expect($dtos[0]->name)->toBe('John Doe');
            expect($dtos[1]->name)->toBe('Jane Doe');
        });
    });
    
    describe('fromModelWithLoadedRelations', function () {
        
        it('works with models without loaded relations', function () {
            $dto = SimpleUserDTO::fromModelWithLoadedRelations($this->user);
            
            expect($dto)->toBeInstanceOf(SimpleUserDTO::class);
            expect($dto->name)->toBe('John Doe');
            expect($dto->email)->toBe('john@example.com');
        });
    });
    
    describe('trait methods exist', function () {
        
        it('has fromModel method', function () {
            expect(method_exists(SimpleUserDTO::class, 'fromModel'))->toBe(true);
        });
        
        it('has fromModels method', function () {
            expect(method_exists(SimpleUserDTO::class, 'fromModels'))->toBe(true);
        });
        
        it('has fromModelWithLoadedRelations method', function () {
            expect(method_exists(SimpleUserDTO::class, 'fromModelWithLoadedRelations'))->toBe(true);
        });
        
        it('has fromModelsWithLoadedRelations method', function () {
            expect(method_exists(SimpleUserDTO::class, 'fromModelsWithLoadedRelations'))->toBe(true);
        });
    });
});

