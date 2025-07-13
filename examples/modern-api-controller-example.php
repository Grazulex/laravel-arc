<?php

/**
 * Modern API Controller example using Laravel Arc's new trait-based system
 * 
 * This example demonstrates how to use behavioral traits in your DTOs
 * and leverage them in API controllers for clean, maintainable code.
 */

namespace App\Http\Controllers\Api;

use App\DTOs\UserDTO;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class UserController extends Controller
{
    /**
     * Display a listing of users with DTO transformation
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->has('with_deleted'), function ($query) {
                $query->withTrashed(); // Works with HasSoftDeletes trait
            })
            ->paginate(15);

        // Convert users to DTOs using the collection method
        $userDtos = UserDTO::collection($users->items());

        return response()->json([
            'data' => $userDtos->map(fn($dto) => $dto->toArray()),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created user with comprehensive validation
     */
    public function store(Request $request): JsonResponse
    {
        // Use ValidatesData trait for validation
        if (UserDTO::fails($request->all())) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => UserDTO::validator($request->all())->errors()
            ], 422);
        }

        // Create user with validated data
        $validated = UserDTO::validate($request->all());
        $user = User::create($validated);

        // Convert to DTO and demonstrate behavioral traits
        $userDto = UserDTO::fromModel($user);

        // Use HasTagging trait if available
        if (method_exists($userDto, 'addTag')) {
            $userDto = $userDto->addTag('new_user');
        }

        // Use HasAuditing trait if available
        if (method_exists($userDto, 'setCreator')) {
            $userDto = $userDto->setCreator(auth()->id());
        }

        // Use HasCaching trait if available
        if (method_exists($userDto, 'cache')) {
            $userDto->cache(3600); // Cache for 1 hour
        }

        return response()->json([
            'message' => 'User created successfully',
            'user' => $userDto->toArray(),
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(User $user): JsonResponse
    {
        $userDto = UserDTO::fromModel($user);

        // Use DtoUtilities trait for selective data exposure
        $response = [
            'user' => $userDto->toArray(),
            'public_profile' => $userDto->only(['name', 'email']),
            'properties' => $userDto->getProperties(),
        ];

        // Add versioning info if HasVersioning trait is available
        if (method_exists($userDto, 'getVersionInfo')) {
            $response['version_info'] = $userDto->getVersionInfo();
        }

        // Add tags if HasTagging trait is available
        if (method_exists($userDto, 'getTags')) {
            $response['tags'] = $userDto->getTags();
        }

        return response()->json($response);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        // Use ValidatesData trait for validation
        if (UserDTO::fails($request->all())) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => UserDTO::validator($request->all())->errors()
            ], 422);
        }

        $originalDto = UserDTO::fromModel($user);
        $validated = UserDTO::validate($request->all());
        
        // Create updated DTO using DtoUtilities trait
        $updatedDto = $originalDto->with($validated);

        // Use HasAuditing trait if available
        if (method_exists($updatedDto, 'setUpdater')) {
            $updatedDto = $updatedDto->setUpdater(auth()->id());
        }

        // Use HasVersioning trait if available
        if (method_exists($updatedDto, 'nextVersion')) {
            $updatedDto = $updatedDto->nextVersion();
        }

        // Update the model
        $user->update($validated);

        // Use HasTimestamps trait if available
        if (method_exists($updatedDto, 'touch')) {
            $updatedDto = $updatedDto->touch();
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $updatedDto->toArray(),
            'changes' => $this->getChanges($originalDto, $updatedDto),
        ]);
    }

    /**
     * Remove the specified user (soft delete if HasSoftDeletes trait is available)
     */
    public function destroy(User $user): JsonResponse
    {
        $userDto = UserDTO::fromModel($user);

        // Use HasAuditing trait if available
        if (method_exists($userDto, 'createAuditTrail')) {
            $auditTrail = $userDto->createAuditTrail('deleted', auth()->id());
            // Save audit trail to your audit system
        }

        // Use HasSoftDeletes trait if available
        if (method_exists($user, 'delete')) {
            $user->delete(); // This will be soft delete if HasSoftDeletes trait is used
        }

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Export users in various formats using ConvertsData trait
     */
    public function export(Request $request): JsonResponse
    {
        $format = $request->get('format', 'json');
        $users = User::all();

        match ($format) {
            'json' => $content = UserDTO::collectionToJson($users),
            'csv' => $content = UserDTO::collectionToCsv($users),
            'xml' => $content = UserDTO::collectionToXml($users),
            'yaml' => $content = UserDTO::collectionToYaml($users),
            'html' => $content = UserDTO::collectionToHtml($users),
            'markdown' => $content = UserDTO::collectionToMarkdownTable($users),
            default => $content = UserDTO::collectionToJson($users),
        };

        return response()->json([
            'format' => $format,
            'content' => $content,
        ]);
    }

    /**
     * Bulk operations using behavioral traits
     */
    public function bulkOperations(Request $request): JsonResponse
    {
        $operation = $request->get('operation');
        $userIds = $request->get('user_ids', []);
        $users = User::whereIn('id', $userIds)->get();
        $userDtos = UserDTO::collection($users);

        switch ($operation) {
            case 'add_tags':
                $tag = $request->get('tag');
                $results = $userDtos->map(function ($dto) use ($tag) {
                    return method_exists($dto, 'addTag') ? $dto->addTag($tag) : $dto;
                });
                break;

            case 'increment_version':
                $results = $userDtos->map(function ($dto) {
                    return method_exists($dto, 'nextVersion') ? $dto->nextVersion() : $dto;
                });
                break;

            case 'cache_all':
                $ttl = $request->get('ttl', 3600);
                $results = $userDtos->map(function ($dto) use ($ttl) {
                    if (method_exists($dto, 'cache')) {
                        $dto->cache($ttl);
                    }
                    return $dto;
                });
                break;

            default:
                $results = $userDtos;
        }

        return response()->json([
            'message' => "Bulk operation '{$operation}' completed",
            'processed' => $results->count(),
            'users' => $results->map(fn($dto) => $dto->toArray()),
        ]);
    }

    /**
     * Get analytics using behavioral traits
     */
    public function analytics(): JsonResponse
    {
        $users = User::all();
        $userDtos = UserDTO::collection($users);

        $analytics = [
            'total_users' => $userDtos->count(),
            'properties' => $userDtos->first()?->getProperties() ?? [],
        ];

        // Add timestamp analytics if HasTimestamps trait is available
        if (method_exists($userDtos->first(), 'getAge')) {
            $analytics['age_stats'] = $userDtos->map(function ($dto) {
                return [
                    'id' => $dto->id,
                    'name' => $dto->name,
                    'age' => $dto->getAge()->totalDays,
                    'recently_created' => $dto->wasRecentlyCreated(),
                ];
            });
        }

        // Add tag analytics if HasTagging trait is available
        if (method_exists($userDtos->first(), 'getTags')) {
            $allTags = $userDtos->flatMap(fn($dto) => $dto->getTags())->unique();
            $analytics['tag_stats'] = $allTags->mapWithKeys(function ($tag) use ($userDtos) {
                return [$tag => $userDtos->filter(fn($dto) => $dto->hasTag($tag))->count()];
            });
        }

        // Add version analytics if HasVersioning trait is available
        if (method_exists($userDtos->first(), 'getVersionInfo')) {
            $analytics['version_stats'] = $userDtos->map(function ($dto) {
                return [
                    'id' => $dto->id,
                    'name' => $dto->name,
                    'version_info' => $dto->getVersionInfo(),
                ];
            });
        }

        return response()->json($analytics);
    }

    /**
     * Helper method to get changes between two DTOs
     */
    private function getChanges(UserDTO $original, UserDTO $updated): array
    {
        $changes = [];
        $properties = $original->getProperties();

        foreach ($properties as $property) {
            if ($original->hasProperty($property) && $updated->hasProperty($property)) {
                $oldValue = $original->getProperty($property);
                $newValue = $updated->getProperty($property);

                if ($oldValue !== $newValue) {
                    $changes[$property] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }
        }

        return $changes;
    }
}

/**
 * Example YAML definition for the UserDTO used in this controller:
 * 
 * # database/dto_definitions/user.yaml
 * header:
 *   dto: UserDTO
 *   model: App\Models\User
 *   namespace: App\DTOs
 *   traits:
 *     - HasTimestamps      # Adds created_at, updated_at fields and methods
 *     - HasUuid           # Adds id field with UUID validation
 *     - HasSoftDeletes    # Adds deleted_at field for soft deletes
 *     - HasVersioning     # Adds version field and versioning methods
 *     - HasTagging        # Adds tags field and tagging methods
 *     - HasAuditing       # Adds created_by, updated_by fields and audit methods
 *     - HasCaching        # Adds caching capabilities
 * 
 * fields:
 *   name:
 *     type: string
 *     required: true
 *     validation: [required, string, min:2, max:100]
 *     transformers: [trim, title_case]
 *   
 *   email:
 *     type: string
 *     required: true
 *     validation: [required, email, unique:users]
 *     transformers: [trim, lowercase]
 *   
 *   status:
 *     type: string
 *     default: "active"
 *     validation: [required, in:active,inactive,pending]
 * 
 * relations:
 *   profile:
 *     type: hasOne
 *     target: App\Models\Profile
 *   
 *   posts:
 *     type: hasMany
 *     target: App\Models\Post
 * 
 * Available methods from behavioral traits:
 * - HasTimestamps: touch(), wasRecentlyCreated(), getAge()
 * - HasUuid: Automatic UUID validation
 * - HasSoftDeletes: Soft delete support
 * - HasVersioning: nextVersion(), isNewerThan(), getVersionInfo()
 * - HasTagging: addTag(), removeTag(), hasTag(), getTags()
 * - HasAuditing: createAuditTrail(), setCreator(), setUpdater(), getAuditInfo()
 * - HasCaching: cache(), clearCache(), getCacheKey(), isCached()
 */