<?php

namespace Grazulex\Arc\Examples;

use Carbon\Carbon;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;
use Grazulex\Arc\Traits\DTOFromModelTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Example DTO showing how to use FromModelTrait to create DTOs from Eloquent models.
 *
 * This example demonstrates:
 * - Basic model to DTO conversion
 * - Including specific relations
 * - Automatic relation detection
 * - Collection handling
 */
class UserDTO extends LaravelArcDTO
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

    #[Property(type: 'nested', class: ProfileDTO::class, required: false)]
    public ?ProfileDTO $profile;

    #[Property(type: 'collection', class: PostDTO::class, required: false, default: [])]
    public array $posts;

    #[Property(type: 'date', required: false)]
    public ?Carbon $created_at;

    #[Property(type: 'date', required: false)]
    public ?Carbon $updated_at;
}

class ProfileDTO extends LaravelArcDTO
{
    use DTOFromModelTrait;

    #[Property(type: 'int', required: false)]
    public ?int $id;

    #[Property(type: 'string', required: false)]
    public ?string $bio;

    #[Property(type: 'string', required: false, validation: 'url')]
    public ?string $website;

    #[Property(type: 'string', required: false)]
    public ?string $avatar_url;
}

class PostDTO extends LaravelArcDTO
{
    use DTOFromModelTrait;

    #[Property(type: 'int', required: false)]
    public ?int $id;

    #[Property(type: 'string', required: true, validation: 'max:255')]
    public string $title;

    #[Property(type: 'string', required: true)]
    public string $content;

    #[Property(type: 'bool', required: false, default: false)]
    public bool $is_published;

    #[Property(type: 'date', required: false)]
    public ?Carbon $published_at;
}

/**
 * Usage examples - these would typically be in your controllers or services.
 */
class FromModelUsageExamples
{
    /**
     * Example 1: Basic model to DTO conversion.
     */
    public function basicConversion(): void
    {
        // Assuming you have a User model instance
        // $user = User::find(1);
        // $userDTO = UserDTO::fromModel($user);
        //
        // echo $userDTO->name; // Direct property access
        // echo $userDTO->email;
    }

    /**
     * Example 2: Include specific relations.
     */
    public function withSpecificRelations(): void
    {
        // Load model with relations
        // $user = User::with(['profile', 'posts'])->find(1);
        //
        // // Create DTO with specific relations
        // $userDTO = UserDTO::fromModel($user, ['profile', 'posts']);
        //
        // // Access nested DTOs
        // echo $userDTO->profile?->bio;
        // echo count($userDTO->posts); // Array of PostDTO instances
    }

    /**
     * Example 3: Automatic relation detection.
     */
    public function withAutoRelationDetection(): void
    {
        // Load model with relations
        // $user = User::with(['profile', 'posts', 'comments'])->find(1);
        //
        // // Create DTO with all loaded relations automatically
        // $userDTO = UserDTO::fromModelWithLoadedRelations($user);
        //
        // // All loaded relations are included
        // echo $userDTO->profile?->website;
    }

    /**
     * Example 4: Convert collection of models.
     */
    public function convertCollection(): void
    {
        // Load collection with relations
        // $users = User::with('profile')->where('is_active', true)->get();
        //
        // // Convert to DTOs
        // $userDTOs = UserDTO::fromModels($users, ['profile']);
        //
        // // Process array of DTOs
        // foreach ($userDTOs as $userDTO) {
        //     echo $userDTO->name . ' - ' . $userDTO->profile?->bio;
        // }
    }

    /**
     * Example 5: Use in API controller.
     */
    public function apiControllerExample(): void
    {
        // In your controller:
        //
        // public function show(User $user)
        // {
        //     $user->load(['profile', 'posts']);
        //     $userDTO = UserDTO::fromModel($user, ['profile', 'posts']);
        //
        //     return response()->json($userDTO->toArray());
        // }
        //
        // public function index()
        // {
        //     $users = User::with('profile')->paginate(10);
        //     $userDTOs = UserDTO::fromModels($users->getCollection(), ['profile']);
        //
        //     return response()->json([
        //         'data' => $userDTOs,
        //         'pagination' => $users->toArray()
        //     ]);
        // }
    }

    /**
     * Example 6: Use in service layer.
     */
    public function serviceLayerExample(): void
    {
        // In your service:
        //
        // class UserService
        // {
        //     public function getUserWithProfile(int $userId): UserDTO
        //     {
        //         $user = User::with('profile')->findOrFail($userId);
        //         return UserDTO::fromModel($user, ['profile']);
        //     }
        //
        //     public function getActiveUsersWithPosts(): array
        //     {
        //         $users = User::with('posts')
        //             ->where('is_active', true)
        //             ->get();
        //
        //         return UserDTO::fromModels($users, ['posts']);
        //     }
        // }
    }
}
