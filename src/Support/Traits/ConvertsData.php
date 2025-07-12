<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits;

use Grazulex\LaravelArc\Support\DTOCollection;
use Illuminate\Support\Collection;

/**
 * Trait that provides data conversion functionality for DTOs.
 * 
 * Classes using this trait must implement a toArray() method.
 */
trait ConvertsData
{
    /**
     * Convert the DTO to an array.
     * This method must be implemented by the class using this trait.
     *
     * @return array
     */
    abstract public function toArray(): array;

    /**
     * Create a DTO instance from a model.
     * This method must be implemented by the class using this trait.
     *
     * @param  mixed  $model  The model to convert
     * @return static
     */
    abstract public static function fromModel($model): static;
    /**
     * Convert a collection of models to a DTOCollection.
     *
     * @param  iterable  $models  The models to convert
     * @return DTOCollection<int, static>
     */
    public static function fromModels(iterable $models): DTOCollection
    {
        $collection = new Collection($models);
        return new DTOCollection($collection->map(fn ($model) => static::fromModel($model)));
    }

    /**
     * Convert a collection of models to a DTOCollection.
     * Alias for fromModels() to provide the collection() method interface.
     *
     * @param  iterable  $models  The models to convert
     * @return DTOCollection<int, static>
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
     * @throws \JsonException If JSON encoding fails
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options | JSON_THROW_ON_ERROR);
    }

    /**
     * Convert the DTO to a collection.
     *
     * @return DTOCollection<int, mixed>
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
