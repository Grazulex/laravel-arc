# FromModelTrait - Create DTOs from Eloquent Models

The `FromModelTrait` provides convenient methods to create DTO instances from Eloquent models with support for relations and collections.

## 🚀 Quick Start

### 1. Add the trait to your DTO

```php
use Grazulex\Arc\LaravelArcDTO;
use Grazulex\Arc\Traits\FromModelTrait;
use Grazulex\Arc\Attributes\Property;

class UserDTO extends LaravelArcDTO
{
    use FromModelTrait;
    
    #[Property(type: 'string', required: true)]
    public string $name;
    
    #[Property(type: 'string', required: true, validation: 'email')]
    public string $email;
    
    #[Property(type: 'int', required: false)]
    public ?int $age;
}
```

### 2. Create DTO from model

```php
// Get an Eloquent model
$user = User::find(1);

// Convert to DTO
$userDTO = UserDTO::fromModel($user);

// Access DTO properties
echo $userDTO->name;
echo $userDTO->email;
```

## 📋 Available Methods

### `fromModel(Model $model, array $relations = []): static`

Create a DTO instance from an Eloquent model.

```php
// Basic usage
$userDTO = UserDTO::fromModel($user);

// With specific relations
$user = User::with(['profile', 'posts'])->find(1);
$userDTO = UserDTO::fromModel($user, ['profile', 'posts']);
```

### `fromModels(Collection $models, array $relations = []): array`

Create multiple DTO instances from a collection of models.

```php
$users = User::with('profile')->get();
$userDTOs = UserDTO::fromModels($users, ['profile']);

// Returns array of UserDTO instances
foreach ($userDTOs as $userDTO) {
    echo $userDTO->name;
}
```

### `fromModelWithLoadedRelations(Model $model): static`

Create a DTO instance with automatic relation detection.

```php
// Load relations
$user = User::with(['profile', 'posts', 'comments'])->find(1);

// All loaded relations are automatically included
$userDTO = UserDTO::fromModelWithLoadedRelations($user);
```

### `fromModelsWithLoadedRelations(Collection $models): array`

Create multiple DTO instances with automatic relation detection.

```php
$users = User::with(['profile', 'posts'])->get();
$userDTOs = UserDTO::fromModelsWithLoadedRelations($users);
```

## 🔗 Working with Relations

### Single Relations (HasOne, BelongsTo)

```php
class ProfileDTO extends LaravelArcDTO
{
    use FromModelTrait;
    
    #[Property(type: 'string', required: false)]
    public ?string $bio;
    
    #[Property(type: 'string', required: false)]
    public ?string $website;
}

class UserDTO extends LaravelArcDTO
{
    use FromModelTrait;
    
    #[Property(type: 'string', required: true)]
    public string $name;
    
    #[Property(type: 'nested', class: ProfileDTO::class, required: false)]
    public ?ProfileDTO $profile;
}

// Usage
$user = User::with('profile')->find(1);
$userDTO = UserDTO::fromModel($user, ['profile']);

// Access nested DTO
echo $userDTO->profile?->bio;
```

### Collection Relations (HasMany, BelongsToMany)

```php
class PostDTO extends LaravelArcDTO
{
    use FromModelTrait;
    
    #[Property(type: 'string', required: true)]
    public string $title;
    
    #[Property(type: 'string', required: true)]
    public string $content;
}

class UserDTO extends LaravelArcDTO
{
    use FromModelTrait;
    
    #[Property(type: 'string', required: true)]
    public string $name;
    
    #[Property(type: 'collection', class: PostDTO::class, required: false, default: [])]
    public array $posts;
}

// Usage
$user = User::with('posts')->find(1);
$userDTO = UserDTO::fromModel($user, ['posts']);

// Access collection of DTOs
foreach ($userDTO->posts as $post) {
    echo $post->title; // Each item is a PostDTO instance
}
```

## 🎯 Usage Patterns

### API Controllers

```php
class UserController extends Controller
{
    public function show(User $user)
    {
        $user->load(['profile', 'posts']);
        $userDTO = UserDTO::fromModel($user, ['profile', 'posts']);
        
        return response()->json($userDTO->toArray());
    }
    
    public function index()
    {
        $users = User::with('profile')->paginate(10);
        $userDTOs = UserDTO::fromModels($users->getCollection(), ['profile']);
        
        return response()->json([
            'data' => array_map(fn($dto) => $dto->toArray(), $userDTOs),
            'pagination' => $users->toArray()
        ]);
    }
}
```

### Service Layer

```php
class UserService
{
    public function getUserWithProfile(int $userId): UserDTO
    {
        $user = User::with('profile')->findOrFail($userId);
        return UserDTO::fromModel($user, ['profile']);
    }
    
    public function getActiveUsersWithPosts(): array
    {
        $users = User::with('posts')
            ->where('is_active', true)
            ->get();
            
        return UserDTO::fromModels($users, ['posts']);
    }
}
```

### Repository Pattern

```php
class UserRepository
{
    public function findAsDTO(int $id): ?UserDTO
    {
        $user = User::with(['profile', 'posts'])->find($id);
        
        return $user ? UserDTO::fromModelWithLoadedRelations($user) : null;
    }
    
    public function getAllActiveAsDTO(): array
    {
        $users = User::with('profile')
            ->where('is_active', true)
            ->get();
            
        return UserDTO::fromModels($users, ['profile']);
    }
}
```

## ⚠️ Important Notes

### Relations Must Be Loaded

Relations must be explicitly loaded before calling `fromModel` with relations:

```php
// ✅ Good - relations are loaded
$user = User::with(['profile', 'posts'])->find(1);
$userDTO = UserDTO::fromModel($user, ['profile', 'posts']);

// ❌ Bad - relations not loaded, will be ignored
$user = User::find(1);
$userDTO = UserDTO::fromModel($user, ['profile', 'posts']); // relations will be empty
```

### DTO Property Types Must Match

Make sure your DTO properties match the model data types:

```php
// If your model has nullable fields, use nullable DTO properties
#[Property(type: 'int', required: false)]
public ?int $age; // Can be null

#[Property(type: 'string', required: true)]
public string $name; // Cannot be null
```

### Automatic Relation Detection

The `fromModelWithLoadedRelations` method includes ALL loaded relations:

```php
// This will include profile, posts, AND comments in the DTO
$user = User::with(['profile', 'posts', 'comments'])->find(1);
$userDTO = UserDTO::fromModelWithLoadedRelations($user);
```

## 🔧 Advanced Example

```php
// Complex DTO with multiple relation types
class UserDTO extends LaravelArcDTO
{
    use FromModelTrait;
    
    // Basic properties
    #[Property(type: 'int', required: false)]
    public ?int $id;
    
    #[Property(type: 'string', required: true)]
    public string $name;
    
    #[Property(type: 'string', required: true, validation: 'email')]
    public string $email;
    
    // Single relation
    #[Property(type: 'nested', class: ProfileDTO::class, required: false)]
    public ?ProfileDTO $profile;
    
    // Collection relation
    #[Property(type: 'collection', class: PostDTO::class, required: false, default: [])]
    public array $posts;
    
    // Enum property
    #[Property(type: 'enum', class: UserStatus::class, default: UserStatus::ACTIVE)]
    public UserStatus $status;
    
    // Date property
    #[Property(type: 'date', required: false)]
    public ?Carbon $created_at;
}

// Usage with complex data
$user = User::with(['profile', 'posts'])->find(1);
$userDTO = UserDTO::fromModel($user, ['profile', 'posts']);

// Access all data types
echo $userDTO->name;                    // string
echo $userDTO->profile?->bio;           // nested DTO
echo count($userDTO->posts);            // collection of DTOs
echo $userDTO->status->value;           // enum
echo $userDTO->created_at->format('Y-m-d'); // date
```

## 🧪 Testing

The trait includes comprehensive tests covering:

- Basic model to DTO conversion
- Single and collection relations
- Automatic relation detection
- Error handling for non-loaded relations
- Integration with real Eloquent models

See `tests/Feature/FromModelTraitTest.php` and `tests/Feature/FromModelTraitIntegrationTest.php` for examples.

---

**🎉 The FromModelTrait makes it easy to bridge the gap between Eloquent models and Laravel Arc DTOs!**

