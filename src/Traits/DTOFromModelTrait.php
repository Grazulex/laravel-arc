<?php

namespace Grazulex\Arc\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use function in_array;

use ReflectionClass;
use ReflectionException;

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

                /** @var Collection<(int|string), Model> $relationData */
                if ($relationData instanceof Collection) {
                    // Handle collection relations (HasMany, BelongsToMany)
                    // @var array<int|string, array|static>
                    $data[$relation] = $relationData->map(function (Model $item) use ($relation): array|static {
                        $relatedDTOClass = self::getRelatedDTOClass($relation);

                        return $relatedDTOClass ? $relatedDTOClass::fromModel($item) : $item->toArray();
                    })->toArray();
                } elseif ($relationData instanceof Model) {
                    // Handle single model relations (HasOne, BelongsTo)
                    $relatedDTOClass = self::getRelatedDTOClass($relation);
                    $data[$relation] = $relatedDTOClass ? $relatedDTOClass::fromModel($relationData) : $relationData->toArray();
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

            /** @var Collection<(int|string), Model> $relationData */
            if ($relationData instanceof Collection) {
                // @var array<int|string, array|static>
                $data[$relation] = $relationData->map(function (Model $item) use ($relation): array|static {
                    $relatedDTOClass = self::getRelatedDTOClass($relation);

                    return $relatedDTOClass ? $relatedDTOClass::fromModel($item) : $item->toArray();
                })->toArray();
            } elseif ($relationData instanceof Model) {
                $relatedDTOClass = self::getRelatedDTOClass($relation);
                $data[$relation] = $relatedDTOClass ? $relatedDTOClass::fromModel($relationData) : $relationData->toArray();
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

    /**
     * Get the DTO class for a given relation name.
     *
     * @param string $relation Relation name
     *
     * @return null|string The DTO class name or null if not found
     */
    private static function getRelatedDTOClass(string $relation): ?string
    {
        // Reflect on the current DTO class
        $reflection = new ReflectionClass(static::class);

        // Look for a property matching the relation name
        try {
            $property = $reflection->getProperty($relation);

            // Get Property attribute
            $attributes = $property->getAttributes(\Grazulex\Arc\Attributes\Property::class);

            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();
                if (in_array($instance->type, ['nested', 'relation', 'dto'], true) && $instance->class) {
                    return $instance->class;
                }
            }
        } catch (ReflectionException $e) {
            // Property not found, try to infer from naming convention
            $guessedClass = '\\App\\Data\\' . ucfirst(rtrim($relation, 's')) . 'DTO';
            if (class_exists($guessedClass)) {
                return $guessedClass;
            }
        }

        return null;
    }
}
