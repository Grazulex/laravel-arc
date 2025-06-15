<?php

namespace Grazulex\Arc\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait to create DTOs from Eloquent models.
 *
 * This trait provides convenient methods to create DTO instances from
 * Eloquent models with support for relations and collections.
 *
 * @example
 * ```php
 * class UserDTO extends LaravelArcDTO
 * {
 *     use FromModelTrait;
 *
 *     #[Property(type: 'string', required: true)]
 *     public string $name;
 * }
 *
 * // Create DTO from model
 * $user = User::with('profile')->find(1);
 * $userDTO = UserDTO::fromModel($user, ['profile']);
 *
 * // Create DTOs from collection
 * $users = User::with('profile')->get();
 * $userDTOs = UserDTO::fromModels($users, ['profile']);
 * ```
 */
trait DTOFromModelTrait
{
    /**
     * Create DTO instance from Eloquent model.
     *
     * @param Model $model The Eloquent model to convert
     * @param array<string> $relations Relations to include (must be loaded)
     *
     * @return static New DTO instance
     */
    public static function fromModel(Model $model, array $relations = []): static
    {
        $data = $model->toArray();

        // Include loaded relations if specified
        foreach ($relations as $relation) {
            if ($model->relationLoaded($relation)) {
                $relationData = $model->{$relation};

                if ($relationData instanceof Collection) {
                    // Handle collection relations (HasMany, BelongsToMany)
                    $data[$relation] = $relationData->toArray();
                } elseif ($relationData instanceof Model) {
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

    /**
     * Create multiple DTO instances from collection of models.
     *
     * @param Collection<int, Model> $models Collection of Eloquent models
     * @param array<string> $relations Relations to include (must be loaded)
     *
     * @return array<static> Array of DTO instances
     */
    public static function fromModels(Collection $models, array $relations = []): array
    {
        return $models->map(fn (Model $model) => static::fromModel($model, $relations))->toArray();
    }

    /**
     * Create DTO instance from model with automatic relation detection.
     *
     * This method automatically includes all loaded relations without
     * needing to specify them explicitly.
     *
     * @param Model $model The Eloquent model to convert
     *
     * @return static New DTO instance
     */
    public static function fromModelWithLoadedRelations(Model $model): static
    {
        $data = $model->toArray();

        // Get all loaded relations
        $loadedRelations = array_keys($model->getRelations());

        // Include all loaded relations
        foreach ($loadedRelations as $relation) {
            $relationData = $model->{$relation};

            if ($relationData instanceof Collection) {
                $data[$relation] = $relationData->toArray();
            } elseif ($relationData instanceof Model) {
                $data[$relation] = $relationData->toArray();
            } else {
                $data[$relation] = $relationData;
            }
        }

        return new static($data);
    }

    /**
     * Create multiple DTO instances with automatic relation detection.
     *
     * @param Collection<int, Model> $models Collection of Eloquent models
     *
     * @return array<static> Array of DTO instances
     */
    public static function fromModelsWithLoadedRelations(Collection $models): array
    {
        return $models->map(fn (Model $model) => static::fromModelWithLoadedRelations($model))->toArray();
    }
}
