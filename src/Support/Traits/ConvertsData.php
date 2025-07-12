<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits;

use Grazulex\LaravelArc\Support\DtoCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

/**
 * Trait that provides data conversion functionality for DTOs.
 */
trait ConvertsData
{
    /**
     * Convert a collection of models to a specialized DTO collection.
     *
     * @param  iterable  $models  The models to convert
     * @return DtoCollection<int, static> Specialized DTO collection
     */
    public static function fromModels(iterable $models): DtoCollection
    {
        $dtos = collect($models)->map(fn ($model) => static::fromModel($model));

        return new DtoCollection($dtos);
    }

    /**
     * Convert a collection of models to a standard collection of DTOs.
     *
     * @param  iterable  $models  The models to convert
     * @return Collection<int, static> Standard collection of DTOs
     */
    public static function fromModelsAsCollection(iterable $models): Collection
    {
        return collect($models)->map(fn ($model) => static::fromModel($model));
    }

    /**
     * Convert a paginated collection of models to DTOs with pagination info.
     *
     * @param  LengthAwarePaginator|Paginator  $paginator  The paginated models
     * @return array Paginated DTOs with meta information
     */
    public static function fromPaginator(LengthAwarePaginator|Paginator $paginator): array
    {
        $dtos = static::fromModels($paginator->items());

        $result = [
            'data' => $dtos->toArray(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
        ];

        // Add total info if available (LengthAwarePaginator)
        if ($paginator instanceof LengthAwarePaginator) {
            $result['meta'] = array_merge($result['meta'], [
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ]);
        }

        return $result;
    }

    /**
     * Convert a collection of models to a JSON collection (similar to Laravel Resources).
     *
     * @param  iterable  $models  The models to convert
     * @return string JSON representation of the DTO collection
     */
    public static function collectionToJson(iterable $models): string
    {
        $dtos = static::fromModels($models);

        return json_encode([
            'data' => $dtos->map(fn ($dto) => $dto->toArray())->toArray(),
        ]);
    }

    /**
     * Convert the DTO to JSON.
     *
     * @param  int  $options  JSON encoding options
     * @return string The JSON representation
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Convert the DTO to a collection.
     *
     * @return Collection<string, mixed> The collection representation
     */
    public function toCollection(): Collection
    {
        return collect($this->toArray());
    }

    /**
     * Get only the specified keys from the DTO.
     *
     * @param  array  $keys  The keys to include
     * @return array The filtered array
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->toArray(), array_flip($keys));
    }

    /**
     * Get all keys except the specified ones from the DTO.
     *
     * @param  array  $keys  The keys to exclude
     * @return array The filtered array
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->toArray(), array_flip($keys));
    }
}
