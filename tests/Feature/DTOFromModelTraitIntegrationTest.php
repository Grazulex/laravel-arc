<?php

use Carbon\Carbon;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;
use Grazulex\Arc\Traits\DTOFromModelTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * @property Model $testModel
 * @property \Illuminate\Foundation\Application $app
 */

/**
 * Integration test DTO for real model conversion.
 */
class RealUserDTO extends LaravelArcDTO
{
    use DTOFromModelTrait;
    
    #[Property(type: 'int', required: false)]
    public ?int $id;
    
    #[Property(type: 'string', required: true, validation: 'max:255')]
    public string $name;
    
    #[Property(type: 'string', required: true, validation: 'email')]
    public string $email;
    
    #[Property(type: 'int', required: false, validation: 'min:0|max:150')]
    public ?int $age;
    
    #[Property(type: 'bool', required: false, default: true)]
    public bool $is_active;
    
    #[Property(type: 'date', required: false)]
    public ?Carbon $created_at;
    
    #[Property(type: 'date', required: false)]
    public ?Carbon $updated_at;
}

describe('DTOFromModelTrait Integration', function () {
    uses(RefreshDatabase::class);
    
    beforeEach(function () {
        // Create test table if using SQLite
        if (!Schema::hasTable('test_users')) {
            Schema::create('test_users', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->integer('age')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
        
        // Create test model class
        // Create a real model for testing
        $model = new class extends Model {
            protected $table = 'test_users';
            protected $fillable = ['name', 'email', 'age', 'is_active'];
            /** @var array<string, string> */
            protected $casts = [
                'is_active' => 'boolean',
                'created_at' => 'datetime',
                'updated_at' => 'datetime'
            ];
        };
        
        // Store the model class name for use in tests
        app()->instance('TestModel', get_class($model));
    });
    
    describe('with real Eloquent models', function () {
        
        it('creates DTO from real Eloquent model', function () {
            /** @var class-string<Model> $modelClass */
            $modelClass = app('TestModel');
            /** @var Model $user */
            $user = $modelClass::query()->create([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'age' => 30,
                'is_active' => true
            ]);
            
            // Convert to DTO
            $dto = RealUserDTO::fromModel($user);
            
            expect($dto)->toBeInstanceOf(RealUserDTO::class);
            expect($dto->id)->toBe($user->id);
            expect($dto->name)->toBe('John Doe');
            expect($dto->email)->toBe('john@example.com');
            expect($dto->age)->toBe(30);
            expect($dto->is_active)->toBe(true);
            expect($dto->created_at)->toBeInstanceOf(Carbon::class);
        });
        
        it('creates DTOs from collection of real models', function () {
            /** @var class-string<Model> $modelClass */
            $modelClass = app('TestModel');
            /** @var Model $user1 */
            $user1 = $modelClass::query()->create([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'age' => 30
            ]);
            
            /** @var Model $user2 */
            $user2 = $modelClass::query()->create([
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'age' => 25
            ]);
            
            // Get collection
            /** @var Collection<int, Model> $users */
$users = $modelClass::all();
            
            // Convert to DTOs
            $dtos = RealUserDTO::fromModels($users);
            
            expect($dtos)->toBeArray();
            expect(count($dtos))->toBe(2);
            expect($dtos[0])->toBeInstanceOf(RealUserDTO::class);
            expect($dtos[1])->toBeInstanceOf(RealUserDTO::class);
            expect($dtos[0]->name)->toBe('John Doe');
            expect($dtos[1]->name)->toBe('Jane Smith');
        });
        
        it('works with fromModelWithLoadedRelations on real models', function () {
            /** @var class-string<Model> $modelClass */
            $modelClass = app()->make('TestModel');
            /** @var Model $user */
            $user = $modelClass::query()->create([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'age' => 30
            ]);
            
            // This should work even without any relations loaded
            $dto = RealUserDTO::fromModelWithLoadedRelations($user);
            
            expect($dto)->toBeInstanceOf(RealUserDTO::class);
            expect($dto->name)->toBe('John Doe');
            expect($dto->email)->toBe('john@example.com');
        });
    });
    
    describe('trait availability', function () {
        
        it('confirms all trait methods are available on real DTO', function () {
            $methods = ['fromModel', 'fromModels', 'fromModelWithLoadedRelations', 'fromModelsWithLoadedRelations'];
            
            foreach ($methods as $method) {
                expect(method_exists(RealUserDTO::class, $method))
                    ->toBe(true, "Method {$method} should exist on RealUserDTO");
            }
        });
    });
});

