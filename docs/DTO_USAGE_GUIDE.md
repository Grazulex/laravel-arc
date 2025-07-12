# DTO Usage Guide

This comprehensive guide demonstrates how to use Laravel Arc generated DTOs in your Laravel applications. After creating and generating your DTOs, this guide shows you how to leverage their full potential in controllers, services, actions, requests, and other Laravel components.

## Table of Contents

- [Generated DTO Methods Overview](#generated-dto-methods-overview)
- [Using DTOs in Controllers](#using-dtos-in-controllers)
- [Using DTOs in Form Requests](#using-dtos-in-form-requests)
- [Using DTOs in Services](#using-dtos-in-services)
- [Using DTOs in Actions](#using-dtos-in-actions)
- [Using DTOs with API Resources](#using-dtos-with-api-resources)
- [Using DTOs in Jobs and Queues](#using-dtos-in-jobs-and-queues)
- [Using DTOs with Validation](#using-dtos-with-validation)
- [Using DTOs with Relationships](#using-dtos-with-relationships)
- [Using DTOs in Tests](#using-dtos-in-tests)
- [Best Practices and Patterns](#best-practices-and-patterns)
- [Future Features](#future-features)

## Generated DTO Methods Overview

When you generate a DTO using Laravel Arc, you get several powerful methods automatically created:

### Core Methods

Every generated DTO includes these essential methods:

```php
// Constructor with readonly properties
public function __construct(
    public readonly string $name,
    public readonly string $email,
    public readonly ?float $price = null,
    // ... other fields
) {}

// Create DTO from Eloquent model
public static function fromModel(Model $model): self

// Convert DTO to array
public function toArray(): array

// Validation rules (if validation is enabled)
public static function rules(): array

// Validate data against DTO rules
public static function validate(array $data): array
```

### Example Generated UserDTO

Based on this YAML definition:

```yaml
# database/dto_definitions/user.yaml
header:
  dto: UserDTO
  model: App\Models\User

fields:
  id:
    type: uuid
    required: true
  name:
    type: string
    required: true
    rules: [min:2, max:100]
  email:
    type: string
    required: true
    rules: [email, unique:users]
  created_at:
    type: datetime
    required: true

options:
  timestamps: true
  namespace: App\DTOs
```

The generated DTO would look like:

```php
<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\User;
use Carbon\Carbon;

final class UserDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly Carbon $created_at,
        public readonly ?Carbon $updated_at = null,
    ) {}

    public static function fromModel(User $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            created_at: $model->created_at,
            updated_at: $model->updated_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public static function rules(): array
    {
        return [
            'id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'created_at' => ['required', 'date'],
        ];
    }

    public static function validate(array $data): array
    {
        return validator($data, self::rules())->validate();
    }
}
```

## Using DTOs in Controllers

### Basic Controller Usage

```php
<?php

namespace App\Http\Controllers;

use App\DTOs\UserDTO;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function show(User $user): JsonResponse
    {
        // Convert model to DTO
        $userDto = UserDTO::fromModel($user);
        
        return response()->json($userDto->toArray());
    }

    public function store(Request $request): JsonResponse
    {
        // Validate using DTO rules
        $validated = UserDTO::validate($request->all());
        
        // Create user
        $user = User::create($validated);
        
        // Return as DTO
        $userDto = UserDTO::fromModel($user);
        
        return response()->json($userDto->toArray(), 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        // Validate partial updates
        $rules = UserDTO::rules();
        $rules['email'] = ['sometimes', 'email', 'unique:users,email,' . $user->id];
        
        $validated = $request->validate($rules);
        
        $user->update($validated);
        
        return response()->json(
            UserDTO::fromModel($user->refresh())->toArray()
        );
    }
}
```

### Advanced Controller with Service Pattern

```php
<?php

namespace App\Http\Controllers;

use App\DTOs\UserDTO;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->getAllUsers(
            page: $request->input('page', 1),
            limit: $request->input('limit', 15)
        );

        // Convert collection to DTO array
        $userDtos = $users->map(fn(User $user) => UserDTO::fromModel($user));

        return response()->json([
            'data' => $userDtos->map(fn(UserDTO $dto) => $dto->toArray()),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $userDto = $this->userService->createUser($request->all());
            
            return response()->json($userDto->toArray(), 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }
}
```

## Using DTOs in Form Requests

### Basic Form Request with DTO

```php
<?php

namespace App\Http\Requests;

use App\DTOs\UserDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return UserDTO::rules();
    }

    public function toUserDTO(): UserDTO
    {
        $validated = $this->validated();
        
        return new UserDTO(
            id: $validated['id'],
            name: $validated['name'],
            email: $validated['email'],
            created_at: now(),
        );
    }
}
```

### Advanced Form Request with Custom Validation

```php
<?php

namespace App\Http\Requests;

use App\DTOs\ProductDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = ProductDTO::rules();
        
        // Add custom business rules
        $rules['price'][] = 'min:0.01';
        $rules['category_id'] = ['required', 'exists:categories,id'];
        
        return $rules;
    }

    public function messages(): array
    {
        return [
            'price.min' => 'Product price must be at least $0.01',
            'category_id.exists' => 'Selected category does not exist',
        ];
    }

    public function toProductDTO(): ProductDTO
    {
        $validated = $this->validated();
        
        return new ProductDTO(
            id: $validated['id'],
            name: $validated['name'],
            price: $validated['price'],
            category_id: $validated['category_id'],
            is_active: $validated['is_active'] ?? true,
            created_at: now(),
        );
    }
}
```

## Using DTOs in Services

### User Service with DTO

```php
<?php

namespace App\Services;

use App\DTOs\UserDTO;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserService
{
    public function getAllUsers(int $page = 1, int $limit = 15): LengthAwarePaginator
    {
        return User::paginate($limit, ['*'], 'page', $page);
    }

    public function createUser(array $data): UserDTO
    {
        $validated = UserDTO::validate($data);
        
        $user = User::create($validated);
        
        return UserDTO::fromModel($user);
    }

    public function updateUser(int $userId, array $data): UserDTO
    {
        $user = User::findOrFail($userId);
        
        // Validate with DTO rules
        $validated = UserDTO::validate($data);
        
        $user->update($validated);
        
        return UserDTO::fromModel($user->refresh());
    }

    public function findUsersByEmail(string $email): Collection
    {
        return User::where('email', 'like', "%{$email}%")
            ->get()
            ->map(fn(User $user) => UserDTO::fromModel($user));
    }

    public function getUserProfile(int $userId): UserDTO
    {
        $user = User::with(['profile', 'addresses'])
            ->findOrFail($userId);
        
        return UserDTO::fromModel($user);
    }
}
```

### E-commerce Service with Multiple DTOs

```php
<?php

namespace App\Services;

use App\DTOs\OrderDTO;
use App\DTOs\ProductDTO;
use App\DTOs\CustomerDTO;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;

class OrderService
{
    public function createOrder(array $orderData): OrderDTO
    {
        // Validate order data
        $validated = OrderDTO::validate($orderData);
        
        // Create order
        $order = Order::create($validated);
        
        // Attach products
        if (isset($orderData['products'])) {
            $order->products()->attach($orderData['products']);
        }
        
        return OrderDTO::fromModel($order->load('products', 'customer'));
    }

    public function getOrderWithDetails(int $orderId): array
    {
        $order = Order::with(['products', 'customer'])
            ->findOrFail($orderId);
        
        return [
            'order' => OrderDTO::fromModel($order),
            'products' => $order->products->map(fn(Product $product) => 
                ProductDTO::fromModel($product)
            ),
            'customer' => CustomerDTO::fromModel($order->customer),
        ];
    }

    public function calculateOrderTotal(OrderDTO $orderDto): float
    {
        // Access DTO properties directly
        $total = $orderDto->subtotal + $orderDto->tax_amount;
        
        if ($orderDto->discount_amount) {
            $total -= $orderDto->discount_amount;
        }
        
        return $total;
    }
}
```

## Using DTOs in Actions

### Single Action Controllers

```php
<?php

namespace App\Http\Controllers;

use App\DTOs\UserDTO;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GetUserProfileAction extends Controller
{
    public function __invoke(Request $request, int $userId): JsonResponse
    {
        $user = User::with(['profile', 'addresses', 'orders'])
            ->findOrFail($userId);
        
        $userDto = UserDTO::fromModel($user);
        
        return response()->json([
            'user' => $userDto->toArray(),
            'meta' => [
                'total_orders' => $user->orders->count(),
                'last_login' => $user->last_login_at,
            ]
        ]);
    }
}
```

### Action with DTO Transformation

```php
<?php

namespace App\Actions;

use App\DTOs\ProductDTO;
use App\Models\Product;
use Illuminate\Support\Collection;

class GetFeaturedProductsAction
{
    public function execute(int $limit = 10): Collection
    {
        return Product::where('is_featured', true)
            ->where('is_active', true)
            ->orderBy('featured_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn(Product $product) => ProductDTO::fromModel($product));
    }

    public function getAsArray(int $limit = 10): array
    {
        return $this->execute($limit)
            ->map(fn(ProductDTO $dto) => $dto->toArray())
            ->toArray();
    }
}
```

## Using DTOs with API Resources

### API Resource with DTO

```php
<?php

namespace App\Http\Resources;

use App\DTOs\UserDTO;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        // Convert model to DTO first
        $userDto = UserDTO::fromModel($this->resource);
        
        return [
            'id' => $userDto->id,
            'name' => $userDto->name,
            'email' => $userDto->email,
            'created_at' => $userDto->created_at->toISOString(),
            'updated_at' => $userDto->updated_at?->toISOString(),
            'profile_url' => route('users.show', $userDto->id),
        ];
    }
}
```

### Collection Resource with DTOs

```php
<?php

namespace App\Http\Resources;

use App\DTOs\ProductDTO;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'data' => $this->collection->map(function ($product) {
                $productDto = ProductDTO::fromModel($product);
                
                return [
                    'id' => $productDto->id,
                    'name' => $productDto->name,
                    'price' => $productDto->price,
                    'formatted_price' => '$' . number_format($productDto->price, 2),
                    'is_active' => $productDto->is_active,
                    'category' => $productDto->category_id,
                ];
            }),
            'meta' => [
                'total' => $this->collection->count(),
                'currency' => 'USD',
            ],
        ];
    }
}
```

## Using DTOs in Jobs and Queues

### Job with DTO

```php
<?php

namespace App\Jobs;

use App\DTOs\UserDTO;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly UserDTO $userDto
    ) {}

    public function handle(EmailService $emailService): void
    {
        $emailService->sendWelcomeEmail([
            'name' => $this->userDto->name,
            'email' => $this->userDto->email,
            'created_at' => $this->userDto->created_at,
        ]);
    }
}
```

### Dispatching Jobs with DTOs

```php
<?php

namespace App\Services;

use App\DTOs\UserDTO;
use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;

class UserRegistrationService
{
    public function register(array $userData): UserDTO
    {
        $validated = UserDTO::validate($userData);
        
        $user = User::create($validated);
        $userDto = UserDTO::fromModel($user);
        
        // Dispatch job with DTO
        SendWelcomeEmailJob::dispatch($userDto);
        
        return $userDto;
    }
}
```

## Using DTOs with Validation

### Advanced Validation Scenarios

```php
<?php

namespace App\Services;

use App\DTOs\OrderDTO;
use App\DTOs\ProductDTO;
use Illuminate\Validation\ValidationException;

class OrderValidationService
{
    public function validateOrder(array $orderData): OrderDTO
    {
        // Basic DTO validation
        $validated = OrderDTO::validate($orderData);
        
        // Custom business logic validation
        $this->validateBusinessRules($validated);
        
        return new OrderDTO(
            id: $validated['id'],
            customer_id: $validated['customer_id'],
            total: $validated['total'],
            status: $validated['status'],
            created_at: now(),
        );
    }

    private function validateBusinessRules(array $data): void
    {
        // Check if total is reasonable
        if ($data['total'] > 10000) {
            throw ValidationException::withMessages([
                'total' => 'Order total cannot exceed $10,000'
            ]);
        }

        // Check if customer exists and is active
        if (!User::where('id', $data['customer_id'])
            ->where('is_active', true)
            ->exists()) {
            throw ValidationException::withMessages([
                'customer_id' => 'Customer is not active'
            ]);
        }
    }
}
```

### Conditional Validation with DTOs

```php
<?php

namespace App\Http\Requests;

use App\DTOs\ProductDTO;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = ProductDTO::rules();
        
        // Make all fields optional for updates
        $rules = array_map(function($rule) {
            if (is_array($rule)) {
                return array_merge(['sometimes'], $rule);
            }
            return ['sometimes', $rule];
        }, $rules);
        
        // Special handling for unique fields
        if ($this->route('product')) {
            $productId = $this->route('product')->id;
            $rules['slug'] = ['sometimes', 'string', 'unique:products,slug,' . $productId];
        }
        
        return $rules;
    }

    public function toProductDTO(): ProductDTO
    {
        $validated = $this->validated();
        $product = $this->route('product');
        
        return new ProductDTO(
            id: $product->id,
            name: $validated['name'] ?? $product->name,
            price: $validated['price'] ?? $product->price,
            slug: $validated['slug'] ?? $product->slug,
            is_active: $validated['is_active'] ?? $product->is_active,
            created_at: $product->created_at,
            updated_at: now(),
        );
    }
}
```

## Using DTOs with Relationships

### Nested DTOs with Relationships

```php
<?php

namespace App\Services;

use App\DTOs\OrderDTO;
use App\DTOs\CustomerDTO;
use App\DTOs\ProductDTO;
use App\Models\Order;

class OrderService
{
    public function getOrderWithRelations(int $orderId): array
    {
        $order = Order::with(['customer', 'products'])
            ->findOrFail($orderId);
        
        return [
            'order' => OrderDTO::fromModel($order),
            'customer' => CustomerDTO::fromModel($order->customer),
            'products' => $order->products->map(fn($product) => 
                ProductDTO::fromModel($product)
            ),
        ];
    }

    public function createOrderWithItems(array $orderData): OrderDTO
    {
        // Validate main order
        $orderValidated = OrderDTO::validate($orderData);
        
        // Validate each product
        $productDtos = collect($orderData['products'])
            ->map(fn($productData) => ProductDTO::validate($productData))
            ->map(fn($validatedProduct) => new ProductDTO(...$validatedProduct));
        
        // Create order
        $order = Order::create($orderValidated);
        
        // Attach products
        $productIds = $productDtos->pluck('id')->toArray();
        $order->products()->attach($productIds);
        
        return OrderDTO::fromModel($order->load('products'));
    }
}
```

## Using DTOs in Tests

### Feature Tests with DTOs

```php
<?php

namespace Tests\Feature;

use App\DTOs\UserDTO;
use App\Models\User;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public function test_can_create_user_with_dto(): void
    {
        $userData = [
            'id' => fake()->uuid(),
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201);
        
        // Verify response structure matches DTO
        $userDto = UserDTO::fromModel(User::first());
        $response->assertJson($userDto->toArray());
    }

    public function test_can_validate_user_data_with_dto(): void
    {
        $invalidData = [
            'name' => 'A', // Too short
            'email' => 'invalid-email',
        ];

        $response = $this->postJson('/api/users', $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email']);
    }
}
```

### Unit Tests with DTOs

```php
<?php

namespace Tests\Unit;

use App\DTOs\UserDTO;
use App\Models\User;
use Tests\TestCase;

class UserDTOTest extends TestCase
{
    public function test_can_create_dto_from_model(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $dto = UserDTO::fromModel($user);

        $this->assertEquals($user->id, $dto->id);
        $this->assertEquals($user->name, $dto->name);
        $this->assertEquals($user->email, $dto->email);
    }

    public function test_can_convert_dto_to_array(): void
    {
        $user = User::factory()->create();
        $dto = UserDTO::fromModel($user);

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('email', $array);
    }

    public function test_dto_validation_rules(): void
    {
        $rules = UserDTO::rules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertContains('required', $rules['name']);
        $this->assertContains('email', $rules['email']);
    }
}
```

## Best Practices and Patterns

### 1. Use DTOs for Data Transfer, Not Business Logic

```php
// ❌ Don't do this
class UserDTO
{
    public function sendNotification(): void
    {
        // Business logic in DTO
    }
}

// ✅ Do this instead
class UserService
{
    public function sendNotification(UserDTO $userDto): void
    {
        // Business logic in service
    }
}
```

### 2. Keep DTOs Immutable

```php
// ✅ Good - readonly properties
class UserDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
    ) {}
}
```

### 3. Use DTOs for API Responses

```php
// ✅ Consistent API responses
class UserController
{
    public function show(User $user): JsonResponse
    {
        return response()->json(
            UserDTO::fromModel($user)->toArray()
        );
    }
}
```

### 4. Validate at Boundaries

```php
// ✅ Validate at entry points
class UserController
{
    public function store(Request $request): JsonResponse
    {
        $validated = UserDTO::validate($request->all());
        
        // Now you can trust the data
        $user = User::create($validated);
        
        return response()->json(
            UserDTO::fromModel($user)->toArray()
        );
    }
}
```

### 5. Use DTOs for Queue Jobs

```php
// ✅ Serialize DTOs for queue jobs
class ProcessOrderJob implements ShouldQueue
{
    public function __construct(
        private readonly OrderDTO $orderDto
    ) {}

    public function handle(): void
    {
        // DTO data is available and type-safe
        $this->processOrder($this->orderDto);
    }
}
```

### 6. Combine DTOs with Form Requests

```php
// ✅ Combine validation and transformation
class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return UserDTO::rules();
    }

    public function toUserDTO(): UserDTO
    {
        $validated = $this->validated();
        
        return new UserDTO(
            id: $validated['id'],
            name: $validated['name'],
            email: $validated['email'],
            created_at: now(),
        );
    }
}
```

## Future Features

The following features are planned for future versions of Laravel Arc and would enhance DTO usage:

### 1. **Automatic Model Synchronization**
```php
// Planned feature - sync DTO changes back to model
$userDto = UserDTO::fromModel($user);
$updatedDto = $userDto->with(['name' => 'New Name']);
$user = $updatedDto->syncToModel($user); // Updates model with DTO data
```

### 2. **DTO Collections**
```php
// Planned feature - native collection support
$users = User::all();
$userDtos = UserDTO::collection($users); // Returns DTOCollection
$userDtos->where('is_active', true)->sortBy('name');
```

### 3. **Automatic API Resource Generation**
```php
// Planned feature - generate API resources from DTOs
php artisan dto:generate-resource UserDTO
// Creates UserResource with DTO integration
```

### 4. **DTO Transformers**
```php
// Planned feature - transform DTOs to different formats
$userDto = UserDTO::fromModel($user);
$publicDto = $userDto->transform(PublicUserDTO::class);
$csvRow = $userDto->transform('csv');
```

### 5. **Advanced Validation Scenarios**
```php
// Planned feature - conditional validation based on DTO state
public static function rules(string $scenario = 'default'): array
{
    return match($scenario) {
        'create' => ['name' => 'required|unique:users'],
        'update' => ['name' => 'sometimes|unique:users,name,{id}'],
        'api' => ['name' => 'required|min:2|max:50'],
    };
}
```

### 6. **DTO Caching**
```php
// Planned feature - cache DTOs for performance
$userDto = UserDTO::fromModel($user)->cache(3600); // Cache for 1 hour
$cachedDto = UserDTO::fromCache($user->id);
```

### 7. **GraphQL Integration**
```php
// Planned feature - auto-generate GraphQL types from DTOs
php artisan dto:generate-graphql UserDTO
// Creates GraphQL type definitions
```

### 8. **DTO Migrations**
```php
// Planned feature - migrate DTO structure changes
php artisan dto:migrate UserDTO --from=v1 --to=v2
// Handles breaking changes in DTO structure
```

### 9. **Event Sourcing Support**
```php
// Planned feature - event sourcing with DTOs
$userDto = UserDTO::fromModel($user);
$event = UserCreatedEvent::fromDTO($userDto);
event($event);
```

### 10. **DTO Serialization Formats**
```php
// Planned feature - multiple serialization formats
$userDto->toJson(); // JSON
$userDto->toXml();  // XML
$userDto->toYaml(); // YAML
$userDto->toCsv();  // CSV
```

These features would make Laravel Arc DTOs even more powerful for complex Laravel applications, providing better integration with Laravel's ecosystem and supporting advanced use cases like microservices, API development, and event-driven architectures.

---

## See Also

- [Getting Started Guide](GETTING_STARTED.md)
- [YAML Schema Documentation](YAML_SCHEMA.md)
- [Field Types Reference](FIELD_TYPES.md)
- [Validation Rules](VALIDATION_RULES.md)
- [CLI Commands](CLI_COMMANDS.md)
- [Examples Collection](../examples/README.md)