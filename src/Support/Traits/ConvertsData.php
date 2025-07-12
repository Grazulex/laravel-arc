<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits;

use Grazulex\LaravelArc\Support\DTOCollection;
use Illuminate\Support\Collection;

/**
 * Trait that provides data conversion functionality for DTOs.
 */
trait ConvertsData
{
    /**
     * Convert a collection of models to a DTOCollection.
     *
     * @param  iterable  $models  The models to convert
     * @return DTOCollection<int, static> DTOCollection of DTOs
     */
    public static function fromModels(iterable $models): DTOCollection
    {
        return new DTOCollection(collect($models)->map(fn ($model) => static::fromModel($model)));
    }

    /**
     * Convert a collection of models to a DTOCollection.
     * Alias for fromModels() to provide the collection() method interface.
     *
     * @param  iterable  $models  The models to convert
     * @return DTOCollection<int, static> DTOCollection of DTOs
     */
    public static function collection(iterable $models): DTOCollection
    {
        return static::fromModels($models);
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
     * @return DTOCollection<string, mixed> The collection representation
     */
    public function toCollection(): DTOCollection
    {
        return new DTOCollection($this->toArray());
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
