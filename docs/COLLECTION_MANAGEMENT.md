# Laravel Arc - DTO Collection Management

## Overview

Laravel Arc provides comprehensive collection management functionality for DTOs, similar to Laravel API Resources but with additional benefits:

- **Automatic conversion**: Easily transform Eloquent models into DTOs
- **Specialized collections**: Use `DtoCollection` for advanced features
- **API format**: Automatic output in standard JSON API format
- **Built-in validation**: Data validation with error handling
- **Collection methods**: Filtering, grouping, pagination, etc.

## Comparison with Laravel Resources

### Laravel Resources (Traditional)
```php
// Controller
return UserResource::collection($users);
return new UserResource($user);

// UserResource
class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
```

### Laravel Arc DTOs (New)
```php
// Controller
return UserDto::fromModels($users)->toArrayResource();
return UserDto::fromModel($user)->toArray();

// UserDto (automatically generated)
class UserDto
{
    use ConvertsData, ValidatesData, DtoUtilities;
    
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
    ) {}
    
    // Automatically generated methods
    public static function fromModel($model): self { ... }
    public function toArray(): array { ... }
    public function isValid(): bool { ... }
}
```

## Basic Usage

### 1. Converting Models to DTOs

```php
// Single model
$user = User::find(1);
$userDto = UserDto::fromModel($user);

// Collection of models
$users = User::all();
$userDtos = UserDto::fromModels($users); // Returns DtoCollection

// Standard Laravel collection
$standardCollection = UserDto::fromModelsAsCollection($users);
```

### 2. API Resource Format

```php
// Get standard API format
$users = User::all();
$userDtos = UserDto::fromModels($users);

// Array format
$apiArray = $userDtos->toArrayResource();
// Result: ['data' => [...]]

// JSON format
$apiJson = $userDtos->toJsonResource();
// Result: '{"data": [...]}'

// With metadata
$apiWithMeta = $userDtos->toArrayResource([
    'total' => 100,
    'page' => 1
]);
// Result: ['data' => [...], 'meta' => [...]]
```

### 3. Pagination Management

```php
// Automatic pagination
$users = User::paginate(15);
$result = UserDto::fromPaginator($users);

return response()->json($result);
// Result:
// {
//   "data": [...],
//   "meta": {
//     "current_page": 1,
//     "per_page": 15,
//     "total": 100,
//     "last_page": 7,
//     "has_more_pages": true
//   }
// }
```

## Advanced Features

### 1. Filtering and Grouping

```php
$users = User::all();
$userDtos = UserDto::fromModels($users);

// Filtering
$activeUsers = $userDtos->where('status', 'active');
$adminUsers = $userDtos->filter(fn($dto) => $dto->role === 'admin');

// Grouping
$groupedByStatus = $userDtos->groupBy('status');
$groupedByRole = $userDtos->groupBy('role');

// Sorting
$sortedByName = $userDtos->sortBy('name');
$sortedByIdDesc = $userDtos->sortByDesc('id');
```

### 2. Field Selection

```php
$userDto = UserDto::fromModel($user);

// Select only certain fields
$minimal = $userDto->only(['id', 'name', 'email']);

// Exclude certain fields
$withoutSensitive = $userDto->except(['password', 'remember_token']);
```

### 3. Built-in Validation

```php
// Validation during creation
$userDto = UserDto::fromArray($request->all());

if (!$userDto->isValid()) {
    return response()->json([
        'message' => 'Validation failed',
        'errors' => $userDto->getErrors()
    ], 422);
}

// Validation of a collection
$userDtos = UserDto::fromModels($users);
$invalid = $userDtos->reject(fn($dto) => $dto->isValid());
```

### 4. Statistics and Aggregations

```php
$userDtos = UserDto::fromModels($users);

// Counting
$total = $userDtos->count();
$activeCount = $userDtos->where('status', 'active')->count();

// Statistics
$stats = [
    'total' => $userDtos->count(),
    'by_status' => $userDtos->groupBy('status')->map->count(),
    'active_percentage' => $userDtos->where('status', 'active')->count() / $userDtos->count() * 100
];
```

## Controller Usage Examples

### Complete API Controller

```php
class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::all();
        $userDtos = UserDto::fromModels($users);
        
        return response()->json(
            $userDtos->toArrayResource()
        );
    }
    
    public function paginated(Request $request): JsonResponse
    {
        $users = User::paginate($request->get('per_page', 15));
        $result = UserDto::fromPaginator($users);
        
        return response()->json($result);
    }
    
    public function filtered(Request $request): JsonResponse
    {
        $users = User::where('status', $request->get('status'))->get();
        $userDtos = UserDto::fromModels($users);
        
        if ($request->has('role')) {
            $userDtos = $userDtos->where('role', $request->get('role'));
        }
        
        return response()->json([
            'data' => $userDtos->toArray(),
            'meta' => ['total' => $userDtos->count()]
        ]);
    }
    
    public function stats(): JsonResponse
    {
        $users = User::all();
        $userDtos = UserDto::fromModels($users);
        
        return response()->json([
            'total' => $userDtos->count(),
            'by_status' => $userDtos->groupBy('status')->map->count(),
            'recent' => $userDtos->sortByDesc('created_at')->take(10)->toArray()
        ]);
    }
}
```

## Automatic Generation

### YAML File

```yaml
# user.yaml
header:
  dto: UserDTO
  table: users
  model: App\Models\User

fields:
  id:
    type: integer
    required: true
    rules: [integer, min:1]
  
  name:
    type: string
    required: true
    rules: [string, max:255]
  
  email:
    type: string
    required: true
    rules: [email, max:255]
  
  status:
    type: string
    default: "active"
    required: true
    rules: [in:active,inactive,pending]

options:
  timestamps: true
  namespace: App\DTOs
```

### Generation

```bash
php artisan dto:generate user.yaml
```

## Available Methods

### ConvertsData Trait

- `fromModels(iterable $models): DtoCollection` - Converts a collection of models
- `fromModelsAsCollection(iterable $models): Collection` - Converts to a standard collection
- `fromPaginator(Paginator $paginator): array` - Handles pagination
- `collectionToJson(iterable $models): string` - Converts directly to JSON API
- `toJson(int $options = 0): string` - Converts a DTO to JSON
- `toCollection(): Collection` - Converts a DTO to collection
- `only(array $keys): array` - Selects certain fields
- `except(array $keys): array` - Excludes certain fields

### DtoCollection Class

- `toArrayResource(array $meta = []): array` - API Resource format
- `toJsonResource(array $meta = []): string` - JSON API Resource format
- Plus all Laravel Collection methods (filter, map, groupBy, etc.)

## Benefits

1. **Strong typing**: Readonly properties with PHP types
2. **Built-in validation**: Automatic validation rules
3. **Performance**: No overhead from Laravel Resources
4. **Flexibility**: Advanced collection methods
5. **Automatic generation**: Less code to write
6. **Compatibility**: Works with all existing Laravel systems

## Migration from Laravel Resources

```php
// Before (Laravel Resources)
class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}

// Usage
return UserResource::collection($users);

// After (Laravel Arc DTOs)
// Automatic DTO generation from YAML
php artisan dto:generate user.yaml

// Usage
return UserDto::fromModels($users)->toArrayResource();
```

This approach provides all the benefits of Laravel Resources plus:
- Strong typing
- Automatic validation
- Advanced collection methods
- Automatic generation
- Improved performance
