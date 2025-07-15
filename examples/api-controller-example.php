<?php

declare(strict_types=1);

/**
 * Laravel Arc - API Controller Example
 *
 * This example demonstrates how to use Laravel Arc DTO in API controllers
 * for robust collection management, similar to Laravel API Resources.
 */

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Example API Controller showing various DTO collection usage patterns
 */
final class ApiController extends Controller
{
    /**
     * Get all users as a DTO collection
     *
     * GET /api/users
     */
    public function index(): JsonResponse
    {
        // Get all users
        $users = User::all();

        // Convert to DTO collection - similar to Laravel Resources
        // Two ways to do this:
        $userDtos = UserDto::fromModels($users);
        // OR using the more intuitive collection() method
        $userDtos = UserDto::collection($users); // Returns DtoCollection

        // Return as JSON resource format
        return response()->json(
            $userDtos->toArrayResource()
        );
    }

    /**
     * Get paginated users with filtering
     *
     * GET /api/users?page=1&per_page=10&status=active
     */
    public function indexPaginated(Request $request): JsonResponse
    {
        // Build query with filtering
        $query = User::query();

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('role')) {
            $query->where('role', $request->get('role'));
        }

        // Paginate results
        $users = $query->paginate(
            $request->get('per_page', 15)
        );

        // Convert to DTO with pagination meta
        $result = UserDto::fromPaginator($users);

        return response()->json($result);
    }

    /**
     * Get users with selected fields only
     *
     * GET /api/users?fields=name,email
     */
    public function indexWithFields(Request $request): JsonResponse
    {
        $users = User::all();
        $userDtos = UserDto::fromModels($users);

        // Apply field selection if requested
        if ($request->has('fields')) {
            $fields = explode(',', $request->get('fields'));
            $userDtos = $userDtos->map(function ($dto) use ($fields) {
                return collect($dto->only($fields));
            });
        }

        return response()->json([
            'data' => $userDtos->toArray(),
        ]);
    }

    /**
     * Get users grouped by status
     *
     * GET /api/users/grouped
     */
    public function indexGrouped(): JsonResponse
    {
        $users = User::all();
        $userDtos = UserDto::collection($users); // Using collection() method

        // Group by status using DtoCollection methods
        $grouped = $userDtos->groupBy('status');

        return response()->json([
            'data' => $grouped->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'users' => $group->map(fn ($dto) => $dto->toArray())->toArray(),
                ];
            }),
        ]);
    }

    /**
     * Get user statistics
     *
     * GET /api/users/stats
     */
    public function stats(): JsonResponse
    {
        $users = User::all();
        $userDtos = UserDto::collection($users); // Using collection() method

        // Use collection methods for statistics
        $stats = [
            'total' => $userDtos->count(),
            'by_status' => $userDtos->groupBy('status')->map->count(),
            'by_role' => $userDtos->groupBy('role')->map->count(),
            'active_percentage' => round(
                ($userDtos->where('status', 'active')->count() / $userDtos->count()) * 100, 2
            ),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Get user orders with nested DTO
     *
     * GET /api/users/{id}/orders
     */
    public function userOrders(int $userId): JsonResponse
    {
        $user = User::with('orders')->findOrFail($userId);
        $userDto = UserDto::fromModel($user);

        // Convert orders to DTO
        $orderDtos = OrderDto::fromModels($user->orders);

        return response()->json([
            'data' => [
                'user' => $userDto->toArray(),
                'orders' => $orderDtos->toArrayResource(),
            ],
        ]);
    }

    /**
     * Bulk operations with DTO
     *
     * POST /api/users/bulk-update
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $userIds = $request->input('user_ids', []);
        $updateData = $request->input('data', []);

        // Get users to update
        $users = User::whereIn('id', $userIds)->get();

        // Convert to DTO before update for validation
        $userDtos = UserDto::fromModels($users);

        // Validate each DTO with the new data
        $validatedDtos = $userDtos->map(function ($dto) use ($updateData) {
            // Create a new DTO with updated data
            $newDto = UserDto::fromArray(
                array_merge($dto->toArray(), $updateData)
            );

            // Validate the new DTO
            if (! $newDto->isValid()) {
                return response()->json([
                    'message' => 'Validation failed for user.',
                    'errors' => $newDto->getErrors(),
                ], 422);
            }

            return $newDto;
        });

        // If all validations pass, update the models
        $users->each(function ($user) use ($updateData) {
            $user->update($updateData);
        });

        // Return updated DTO
        $updatedUsers = User::whereIn('id', $userIds)->get();
        $updatedDtos = UserDto::fromModels($updatedUsers);

        return response()->json([
            'message' => 'Users updated successfully',
            'data' => $updatedDtos->toArrayResource(),
        ]);
    }

    /**
     * Export users to different formats
     *
     * GET /api/users/export?format=json|csv|xml|yaml|markdown
     */
    public function export(Request $request): JsonResponse
    {
        $format = $request->get('format', 'json');
        $users = User::all();

        // Export using Laravel Arc's built-in export methods
        switch ($format) {
            case 'csv':
                $csvData = UserDto::collectionToCsv($users);

                return response()->json([
                    'format' => 'csv',
                    'data' => $csvData,
                ]);

            case 'xml':
                $xmlData = UserDto::collectionToXml($users, 'users', 'user');

                return response()->json([
                    'format' => 'xml',
                    'data' => $xmlData,
                ]);

            case 'yaml':
                $yamlData = UserDto::collectionToYaml($users);

                return response()->json([
                    'format' => 'yaml',
                    'data' => $yamlData,
                ]);

            case 'markdown':
                $markdownData = UserDto::collectionToMarkdownTable($users);

                return response()->json([
                    'format' => 'markdown',
                    'data' => $markdownData,
                ]);

            case 'json':
            default:
                $jsonData = UserDto::collectionToJson($users);

                return response()->json([
                    'format' => 'json',
                    'data' => $jsonData,
                ]);
        }
    }

    /**
     * Advanced filtering and searching
     *
     * GET /api/users/search?q=john&filters[role]=admin&sort=name
     */
    public function search(Request $request): JsonResponse
    {
        $query = User::query();

        // Search
        if ($request->has('q')) {
            $searchTerm = $request->get('q');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        // Filters
        if ($request->has('filters')) {
            foreach ($request->get('filters') as $field => $value) {
                $query->where($field, $value);
            }
        }

        // Sorting
        if ($request->has('sort')) {
            $sort = $request->get('sort');
            $direction = $request->get('direction', 'asc');
            $query->orderBy($sort, $direction);
        }

        $users = $query->get();
        $userDtos = UserDto::fromModels($users);

        return response()->json([
            'data' => $userDtos->toArrayResource(),
            'meta' => [
                'total' => $userDtos->count(),
                'search_term' => $request->get('q'),
                'filters_applied' => $request->get('filters', []),
                'sort' => $request->get('sort'),
            ],
        ]);
    }

    /**
     * Export users in multiple modern formats (NEW in 2025!)
     *
     * GET /api/users/export?format=csv|xml|yaml|toml|markdown
     */
    public function exportInModernFormats(Request $request): mixed
    {
        $users = User::take(10)->get();
        $format = $request->query('format', 'json');

        return match ($format) {
            'json' => response()->json(['data' => UserDto::fromModels($users)->toArray()]),
            'csv' => response(
                UserDto::collectionToCsv($users),
                200,
                ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="users.csv"']
            ),
            'xml' => response(
                UserDto::collectionToXml($users, 'users', 'user'),
                200,
                ['Content-Type' => 'application/xml']
            ),
            'yaml' => response(
                UserDto::collectionToYaml($users),
                200,
                ['Content-Type' => 'application/yaml']
            ),
            'toml' => response(
                collect($users)->map(fn ($user) => UserDto::fromModel($user)->toToml())->implode("\n\n"),
                200,
                ['Content-Type' => 'application/toml']
            ),
            'markdown' => response(
                "# Users Export\n\n".UserDto::collectionToMarkdownTable($users),
                200,
                ['Content-Type' => 'text/markdown']
            ),
            default => response()->json(['error' => 'Unsupported format'], 400),
        };
    }
}

/**
 * Example User DTO class that would be generated by Laravel Arc
 */
final class UserDto
{
    use Grazulex\LaravelArc\Support\Traits\ConvertsData;
    use Grazulex\LaravelArc\Support\Traits\DtoUtilities;
    use Grazulex\LaravelArc\Support\Traits\ValidatesData;

    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $status,
        public readonly string $role,
        public readonly ?string $avatar = null,
        public readonly ?DateTimeImmutable $created_at = null,
        public readonly ?DateTimeImmutable $updated_at = null,
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            status: $model->status,
            role: $model->role,
            avatar: $model->avatar,
            created_at: $model->created_at ? $model->created_at->toImmutable() : null,
            updated_at: $model->updated_at ? $model->updated_at->toImmutable() : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'role' => $this->role,
            'avatar' => $this->avatar,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
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
 * Example Order DTO class
 */
final class OrderDto
{
    use Grazulex\LaravelArc\Support\Traits\ConvertsData;
    use Grazulex\LaravelArc\Support\Traits\DtoUtilities;
    use Grazulex\LaravelArc\Support\Traits\ValidatesData;

    public function __construct(
        public readonly int $id,
        public readonly int $user_id,
        public readonly string $status,
        public readonly float $total,
        public readonly ?DateTimeImmutable $created_at = null,
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            user_id: $model->user_id,
            status: $model->status,
            total: $model->total,
            created_at: $model->created_at ? $model->created_at->toImmutable() : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'total' => $this->total,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}

/**
 * Usage Examples in Routes:
 *
 * Route::get('/api/users', [ApiController::class, 'index']);
 * Route::get('/api/users/paginated', [ApiController::class, 'indexPaginated']);
 * Route::get('/api/users/grouped', [ApiController::class, 'indexGrouped']);
 * Route::get('/api/users/stats', [ApiController::class, 'stats']);
 * Route::get('/api/users/{id}/orders', [ApiController::class, 'userOrders']);
 * Route::post('/api/users/bulk-update', [ApiController::class, 'bulkUpdate']);
 * Route::get('/api/users/export', [ApiController::class, 'export']);
 * Route::get('/api/users/search', [ApiController::class, 'search']);
 * Route::get('/api/users/export-modern', [ApiController::class, 'exportInModernFormats']);
 */
