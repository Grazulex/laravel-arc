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

> **💡 New!** All DTOs now include powerful traits for validation, conversion, and utilities. 
> For complete trait documentation, see our [**Traits Guide**](TRAITS_GUIDE.md).

## Generated DTO Methods Overview

When you generate a DTO using Laravel Arc, you get several powerful methods automatically created, plus additional methods provided by built-in traits:

### Core Methods (Generated)

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
```

### Built-in Trait Methods

Every DTO automatically includes these three powerful traits:

#### 🔍 **ValidatesData Trait**
```php
// Validate data and return validated array
public static function validate(array $data): array

// Create validator instance
public static function validator(array $data): Validator

// Check if validation passes
public static function passes(array $data): bool

// Check if validation fails
public static function fails(array $data): bool
```

#### 🔄 **ConvertsData Trait**
```php
// Convert multiple models to DTOs
public static function fromModels(iterable $models): DtoCollection

// Convert multiple models to DTOs (alias for fromModels)
public static function collection(iterable $models): DtoCollection

// Convert multiple models to standard collection
public static function fromModelsAsCollection(iterable $models): Collection

// Convert DTO to JSON
public function toJson(int $options = 0): string

// Convert DTO to Collection
public function toCollection(): Collection

// Get only specified keys
public function only(array $keys): array

// Get all keys except specified ones
public function except(array $keys): array
```

#### 🛠️ **DtoUtilities Trait**
```php
// Get all property names
public function getProperties(): array

// Check if property exists
public function hasProperty(string $property): bool

// Get property value by name
public function getProperty(string $property): mixed

// Create new instance with modified properties
public function with(array $properties): static

// Compare two DTOs for equality
public function equals(self $other): bool
```

### Example Generated UserDTO

Based on this YAML definition:

```yaml
# database/dto_definitions/user.yaml
header:
  dto: UserDTO
  model: App\Models\User
  namespace: App\DTO
  traits:
    - HasTimestamps
    - HasUuid

fields:
  name:
    type: string
    required: true
    validation: [required, string, min:2, max:100]
  email:
    type: string
    required: true
    validation: [required, email, unique:users]
```

The generated DTO would look like:

```php
<?php

declare(strict_types=1);

namespace App\DTO;

use App\Models\User;
use Carbon\Carbon;
use Grazulex\LaravelArc\Support\Traits\ConvertsData;
use Grazulex\LaravelArc\Support\Traits\DtoUtilities;
use Grazulex\LaravelArc\Support\Traits\ValidatesData;

final class UserDTO
{
    use ConvertsData;
    use DtoUtilities;
    use ValidatesData;

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

    // The validate(), validator(), passes(), fails() methods are provided by ValidatesData trait
    // The fromModels(), toJson(), toCollection(), only(), except() methods are provided by ConvertsData trait
    // The getProperties(), hasProperty(), getProperty(), with(), equals() methods are provided by DtoUtilities trait
}
```

## Using DTOs in Controllers

### Basic Controller Usage

```php
<?php

namespace App\Http\Controllers;

use App\DTO\UserDTO;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::all();
        
        // Convert multiple models to DTOs using ConvertsData trait
        // Two ways to do this:
        $userDtos = UserDTO::fromModels($users);
        // OR
        $userDtos = UserDTO::collection($users); // Returns DtoCollection
        
        return response()->json($userDtos->map(fn($dto) => $dto->toArray()));
    }

    public function show(User $user): JsonResponse
    {
        // Convert model to DTO
        $userDto = UserDTO::fromModel($user);
        
        return response()->json($userDto->toArray());
    }

    public function store(Request $request): JsonResponse
    {
        // Validate using ValidatesData trait
        $validated = UserDTO::validate($request->all());
        
        // Create user
        $user = User::create($validated);
        
        // Return as DTO
        $userDto = UserDTO::fromModel($user);
        
        return response()->json($userDto->toArray(), 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        // Use ValidatesData trait to check if validation passes
        if (!UserDTO::passes($request->all())) {
            return response()->json(['error' => 'Validation failed'], 422);
        }
        
        $validated = UserDTO::validate($request->all());
        $user->update($validated);
        
        return response()->json(
            UserDTO::fromModel($user->refresh())->toArray()
        );
    }

    public function profile(User $user): JsonResponse
    {
        $userDto = UserDTO::fromModel($user);
        
        // Use DtoUtilities trait to filter data
        $publicData = $userDto->only(['name', 'email']);
        $privateData = $userDto->except(['password', 'remember_token']);
        
        return response()->json([
            'public' => $publicData,
            'private' => $privateData,
        ]);
    }
}
```

### Advanced Controller with Service Pattern

```php
<?php

namespace App\Http\Controllers;

use App\DTO\UserDTO;
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

        // Convert collection to DTOs using ConvertsData trait
        // Two ways to do this:
        $userDtos = UserDTO::fromModels($users);
        // OR
        $userDtos = UserDTO::collection($users); // Returns DtoCollection

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
            // Use ValidatesData trait for validation
            if (UserDTO::fails($request->all())) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => UserDTO::validator($request->all())->errors()
                ], 422);
            }

            $userDto = $this->userService->createUser($request->all());
            
            return response()->json($userDto->toArray(), 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function compare(Request $request): JsonResponse
    {
        $user1 = User::find($request->input('user1_id'));
        $user2 = User::find($request->input('user2_id'));

        $dto1 = UserDTO::fromModel($user1);
        $dto2 = UserDTO::fromModel($user2);

        // Use DtoUtilities trait to compare DTOs
        $areEqual = $dto1->equals($dto2);

        return response()->json([
            'user1' => $dto1->toArray(),
            'user2' => $dto2->toArray(),
            'are_equal' => $areEqual,
            'properties' => $dto1->getProperties(),
        ]);
    }
}
```

## Using DTOs in Form Requests

### Basic Form Request with DTO

```php
<?php

namespace App\Http\Requests;

use App\DTO\UserDTO;
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

use App\DTO\ProductDTO;
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

use App\DTO\UserDTO;
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
        // Use ValidatesData trait for validation
        $validated = UserDTO::validate($data);
        
        $user = User::create($validated);
        
        return UserDTO::fromModel($user);
    }

    public function updateUser(int $userId, array $data): UserDTO
    {
        $user = User::findOrFail($userId);
        
        // Use ValidatesData trait to check validation
        if (UserDTO::fails($data)) {
            $validator = UserDTO::validator($data);
            throw new ValidationException($validator);
        }
        
        $validated = UserDTO::validate($data);
        $user->update($validated);
        
        return UserDTO::fromModel($user->refresh());
    }

    public function findUsersByEmail(string $email): Collection
    {
        $users = User::where('email', 'like', "%{$email}%")->get();
        
        // Use ConvertsData trait to convert multiple models
        return UserDTO::fromModels($users);
    }

    public function getUserProfile(int $userId): UserDTO
    {
        $user = User::with(['profile', 'addresses'])
            ->findOrFail($userId);
        
        return UserDTO::fromModel($user);
    }

    public function getUsersAsJson(array $userIds): string
    {
        $users = User::whereIn('id', $userIds)->get();
        $userDtos = UserDTO::fromModels($users);
        
        // Use ConvertsData trait to convert to JSON
        return $userDtos->map(fn($dto) => $dto->toJson())->implode(',');
    }

    public function compareUsers(int $userId1, int $userId2): array
    {
        $user1 = User::findOrFail($userId1);
        $user2 = User::findOrFail($userId2);
        
        $dto1 = UserDTO::fromModel($user1);
        $dto2 = UserDTO::fromModel($user2);
        
        // Use DtoUtilities trait to compare and inspect
        return [
            'are_equal' => $dto1->equals($dto2),
            'properties' => $dto1->getProperties(),
            'user1_has_email' => $dto1->hasProperty('email'),
            'user1_email' => $dto1->getProperty('email'),
            'user1_name_only' => $dto1->only(['name']),
            'user2_without_email' => $dto2->except(['email']),
        ];
    }

    public function createUserVariant(UserDTO $originalDto, array $changes): UserDTO
    {
        // Use DtoUtilities trait to create modified copy
        return $originalDto->with($changes);
    }
}
```

### E-commerce Service with Multiple DTOs

```php
<?php

namespace App\Services;

use App\DTO\OrderDTO;
use App\DTO\ProductDTO;
use App\DTO\CustomerDTO;
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

use App\DTO\UserDTO;
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

use App\DTO\ProductDTO;
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

use App\DTO\UserDTO;
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

use App\DTO\ProductDTO;
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

### Job with DTO and Trait Usage

```php
<?php

namespace App\Jobs;

use App\DTO\UserDTO;
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
        // Use DtoUtilities trait to get only needed properties
        $emailData = $this->userDto->only(['name', 'email']);
        
        // Use ConvertsData trait to convert to JSON for logging
        $userJson = $this->userDto->toJson();
        
        \Log::info('Sending welcome email', ['user' => $userJson]);
        
        $emailService->sendWelcomeEmail([
            'name' => $emailData['name'],
            'email' => $emailData['email'],
            'created_at' => $this->userDto->created_at,
        ]);
    }
}
```

### Advanced Job with DTO Processing

```php
<?php

namespace App\Jobs;

use App\DTO\UserDTO;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessUserBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly array $userIds
    ) {}

    public function handle(): void
    {
        $users = User::whereIn('id', $this->userIds)->get();
        
        // Use ConvertsData trait to convert multiple models
        $userDtos = UserDTO::fromModels($users);
        // OR use collection method for more intuitive syntax
        $userDtos = UserDTO::collection($users); // Returns DtoCollection
        
        foreach ($userDtos as $userDto) {
            // Use DtoUtilities trait to inspect and modify data
            $properties = $userDto->getProperties();
            
            if ($userDto->hasProperty('email')) {
                $email = $userDto->getProperty('email');
                
                // Process each user
                $this->processUser($userDto);
                
                // Create modified version for audit
                $auditDto = $userDto->with(['processed_at' => now()]);
                $this->auditUser($auditDto);
            }
        }
    }

    private function processUser(UserDTO $userDto): void
    {
        // Process individual user
        \Log::info('Processing user', $userDto->except(['password']));
    }

    private function auditUser(UserDTO $userDto): void
    {
        // Use ConvertsData trait to create audit log
        $auditData = $userDto->toCollection();
        // Store audit data...
    }
}
```

### Dispatching Jobs with DTOs

```php
<?php

namespace App\Services;

use App\DTO\UserDTO;
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

Laravel Arc DTOs provide powerful validation capabilities through the `ValidatesData` trait:

```php
<?php

namespace App\Services;

use App\DTO\OrderDTO;
use App\DTO\ProductDTO;
use Illuminate\Validation\ValidationException;

class OrderValidationService
{
    public function validateOrder(array $orderData): OrderDTO
    {
        // Use ValidatesData trait for quick validation check
        if (OrderDTO::fails($orderData)) {
            $validator = OrderDTO::validator($orderData);
            throw new ValidationException($validator);
        }

        // Get validated data using trait method
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

    public function validateOrderWithFeedback(array $orderData): array
    {
        // Use ValidatesData trait to get detailed validation info
        $validator = OrderDTO::validator($orderData);
        
        return [
            'passes' => OrderDTO::passes($orderData),
            'fails' => OrderDTO::fails($orderData),
            'errors' => $validator->errors()->toArray(),
            'validated' => $validator->validated(),
        ];
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

use App\DTO\ProductDTO;
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

use App\DTO\OrderDTO;
use App\DTO\CustomerDTO;
use App\DTO\ProductDTO;
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

### Feature Tests with DTOs and Traits

```php
<?php

namespace Tests\Feature;

use App\DTO\UserDTO;
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

    public function test_can_validate_user_data_with_dto_traits(): void
    {
        $validData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];
        
        $invalidData = [
            'name' => 'A', // Too short
            'email' => 'invalid-email',
        ];

        // Test ValidatesData trait methods
        $this->assertTrue(UserDTO::passes($validData));
        $this->assertFalse(UserDTO::fails($validData));
        
        $this->assertFalse(UserDTO::passes($invalidData));
        $this->assertTrue(UserDTO::fails($invalidData));
        
        // Test validation with HTTP requests
        $response = $this->postJson('/api/users', $invalidData);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_can_use_dto_conversion_traits(): void
    {
        $users = User::factory()->count(3)->create();
        
        // Test ConvertsData trait
        $userDtos = UserDTO::fromModels($users);
        
        $this->assertCount(3, $userDtos);
        $this->assertInstanceOf(UserDTO::class, $userDtos->first());
        
        // Test individual DTO conversion methods
        $userDto = $userDtos->first();
        $json = $userDto->toJson();
        $collection = $userDto->toCollection();
        
        $this->assertJson($json);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection);
        $this->assertEquals($userDto->toArray(), $collection->toArray());
    }

    public function test_can_use_dto_utility_traits(): void
    {
        $user = User::factory()->create();
        $userDto = UserDTO::fromModel($user);
        
        // Test DtoUtilities trait
        $properties = $userDto->getProperties();
        $this->assertIsArray($properties);
        $this->assertContains('name', $properties);
        $this->assertContains('email', $properties);
        
        $this->assertTrue($userDto->hasProperty('name'));
        $this->assertFalse($userDto->hasProperty('nonexistent'));
        
        $this->assertEquals($user->name, $userDto->getProperty('name'));
        
        // Test filtering
        $nameOnly = $userDto->only(['name']);
        $this->assertArrayHasKey('name', $nameOnly);
        $this->assertArrayNotHasKey('email', $nameOnly);
        
        $withoutEmail = $userDto->except(['email']);
        $this->assertArrayNotHasKey('email', $withoutEmail);
        $this->assertArrayHasKey('name', $withoutEmail);
    }
}
```

### Unit Tests with DTOs and Traits

```php
<?php

namespace Tests\Unit;

use App\DTO\UserDTO;
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

    public function test_validates_data_trait_methods(): void
    {
        $validData = [
            'id' => fake()->uuid(),
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'created_at' => now(),
        ];

        $invalidData = [
            'name' => 'A', // Too short
            'email' => 'invalid-email',
        ];

        // Test ValidatesData trait methods
        $this->assertTrue(UserDTO::passes($validData));
        $this->assertFalse(UserDTO::fails($validData));
        
        $this->assertFalse(UserDTO::passes($invalidData));
        $this->assertTrue(UserDTO::fails($invalidData));

        // Test validator method
        $validator = UserDTO::validator($validData);
        $this->assertFalse($validator->fails());
        
        $validator = UserDTO::validator($invalidData);
        $this->assertTrue($validator->fails());
    }

    public function test_converts_data_trait_methods(): void
    {
        $users = User::factory()->count(3)->create();
        
        // Test fromModels method
        $userDtos = UserDTO::fromModels($users);
        $this->assertCount(3, $userDtos);
        $this->assertInstanceOf(UserDTO::class, $userDtos->first());
        
        // Test individual conversion methods
        $userDto = UserDTO::fromModel($users->first());
        
        $json = $userDto->toJson();
        $this->assertJson($json);
        
        $collection = $userDto->toCollection();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection);
        
        $nameOnly = $userDto->only(['name']);
        $this->assertArrayHasKey('name', $nameOnly);
        $this->assertArrayNotHasKey('email', $nameOnly);
        
        $withoutEmail = $userDto->except(['email']);
        $this->assertArrayNotHasKey('email', $withoutEmail);
        $this->assertArrayHasKey('name', $withoutEmail);
    }

    public function test_dto_utilities_trait_methods(): void
    {
        $user = User::factory()->create();
        $userDto = UserDTO::fromModel($user);
        
        // Test property inspection
        $properties = $userDto->getProperties();
        $this->assertIsArray($properties);
        $this->assertContains('name', $properties);
        $this->assertContains('email', $properties);
        
        $this->assertTrue($userDto->hasProperty('name'));
        $this->assertFalse($userDto->hasProperty('nonexistent'));
        
        $this->assertEquals($user->name, $userDto->getProperty('name'));
        
        // Test with method
        $modifiedDto = $userDto->with(['name' => 'Modified Name']);
        $this->assertEquals('Modified Name', $modifiedDto->name);
        $this->assertEquals($user->email, $modifiedDto->email); // Other properties preserved
        
        // Test equals method
        $sameDto = UserDTO::fromModel($user);
        $differentUser = User::factory()->create();
        $differentDto = UserDTO::fromModel($differentUser);
        
        $this->assertTrue($userDto->equals($sameDto));
        $this->assertFalse($userDto->equals($differentDto));
    }

    public function test_dto_utilities_trait_exceptions(): void
    {
        $user = User::factory()->create();
        $userDto = UserDTO::fromModel($user);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Property 'nonexistent' does not exist");
        
        $userDto->getProperty('nonexistent');
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
        // Use DTO trait methods to access data
        $emailData = $userDto->only(['name', 'email']);
        // Send notification with email data
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

// ✅ Use DtoUtilities trait for creating modified copies
$updatedDto = $originalDto->with(['name' => 'New Name']);
```

### 3. Use DTOs for API Responses

```php
// ✅ Consistent API responses with trait methods
class UserController
{
    public function show(User $user): JsonResponse
    {
        $userDto = UserDTO::fromModel($user);
        
        return response()->json(
            $userDto->except(['password', 'remember_token'])
        );
    }
    
    public function index(): JsonResponse
    {
        $users = User::all();
        $userDtos = UserDTO::fromModels($users);
        
        return response()->json([
            'data' => $userDtos->map(fn($dto) => $dto->toArray())
        ]);
    }
}
```

### 4. Validate at Boundaries

```php
// ✅ Use ValidatesData trait for validation
class UserController
{
    public function store(Request $request): JsonResponse
    {
        // Quick validation check
        if (UserDTO::fails($request->all())) {
            return response()->json([
                'errors' => UserDTO::validator($request->all())->errors()
            ], 422);
        }
        
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
// ✅ Serialize DTOs for queue jobs with trait methods
class ProcessOrderJob implements ShouldQueue
{
    public function __construct(
        private readonly OrderDTO $orderDto
    ) {}

    public function handle(): void
    {
        // Use trait methods to access and convert data
        $orderJson = $this->orderDto->toJson();
        $essentialData = $this->orderDto->only(['id', 'total', 'status']);
        
        \Log::info('Processing order', ['order' => $orderJson]);
        
        // Process with essential data
        $this->processOrder($essentialData);
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

### 7. Leverage Trait Methods for Data Processing

```php
// ✅ Use trait methods for data manipulation
class UserService
{
    public function processUserData(UserDTO $userDto): array
    {
        // Get all properties for inspection
        $properties = $userDto->getProperties();
        
        // Check for required properties
        if (!$userDto->hasProperty('email')) {
            throw new InvalidArgumentException('Email is required');
        }
        
        // Get specific property
        $email = $userDto->getProperty('email');
        
        // Create variations
        $publicData = $userDto->only(['name', 'email']);
        $privateData = $userDto->except(['password']);
        
        // Create modified version
        $processedDto = $userDto->with(['processed_at' => now()]);
        
        return [
            'original' => $userDto->toArray(),
            'public' => $publicData,
            'private' => $privateData,
            'processed' => $processedDto->toArray(),
            'properties' => $properties,
        ];
    }
}
```

### 8. Use Trait Methods for Testing

```php
// ✅ Test DTOs with trait methods
class UserTest extends TestCase
{
    public function test_user_dto_functionality(): void
    {
        $user = User::factory()->create();
        $userDto = UserDTO::fromModel($user);
        
        // Test trait methods
        $this->assertTrue($userDto->hasProperty('name'));
        $this->assertEquals($user->name, $userDto->getProperty('name'));
        
        $modifiedDto = $userDto->with(['name' => 'Modified']);
        $this->assertEquals('Modified', $modifiedDto->name);
        $this->assertFalse($userDto->equals($modifiedDto));
        
        // Test conversion methods
        $json = $userDto->toJson();
        $this->assertJson($json);
        
        $collection = $userDto->toCollection();
        $this->assertInstanceOf(Collection::class, $collection);
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

### 2. **Automatic API Resource Generation**
```php
// Planned feature - generate API resources from DTOs
php artisan dto:generate-resource UserDTO
// Creates UserResource with DTO integration
```

### 3. **DTO Transformers**
```php
// Planned feature - transform DTOs to different formats
$userDto = UserDTO::fromModel($user);
$publicDto = $userDto->transform(PublicUserDTO::class);
$csvRow = $userDto->transform('csv');
```

### 4. **Advanced Validation Scenarios**
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

### 5. **DTO Caching**
```php
// Planned feature - cache DTOs for performance
$userDto = UserDTO::fromModel($user)->cache(3600); // Cache for 1 hour
$cachedDto = UserDTO::fromCache($user->id);
```

### 6. **GraphQL Integration**
```php
// Planned feature - auto-generate GraphQL types from DTOs
php artisan dto:generate-graphql UserDTO
// Creates GraphQL type definitions
```

### 7. **DTO Migrations**
```php
// Planned feature - migrate DTO structure changes
php artisan dto:migrate UserDTO --from=v1 --to=v2
// Handles breaking changes in DTO structure
```

### 8. **Event Sourcing Support**
```php
// Planned feature - event sourcing with DTOs
$userDto = UserDTO::fromModel($user);
$event = UserCreatedEvent::fromDTO($userDto);
event($event);
```

### 9. **DTO Serialization Formats**
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
- [**Traits Guide**](TRAITS_GUIDE.md) - **Complete guide to ValidatesData, ConvertsData, and DtoUtilities traits**
- [YAML Schema Documentation](YAML_SCHEMA.md)
- [Field Types Reference](FIELD_TYPES.md)
- [Validation Rules](VALIDATION_RULES.md)
- [CLI Commands](CLI_COMMANDS.md)
- [Examples Collection](../examples/README.md)