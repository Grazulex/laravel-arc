<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support;

use Illuminate\Support\Collection;

/**
 * Collection class specifically designed for DTOs.
 * Provides enhanced functionality for working with collections of DTOs.
 */
class DTOCollection extends Collection
{
    /**
     * Filter DTOs by a specific property value.
     *
     * @param  string  $property  The property to filter by
     * @param  mixed  $value  The value to match
     * @return static
     */
    public function where(string $property, mixed $value): static
    {
        return $this->filter(function ($dto) use ($property, $value) {
            return $this->getPropertyValue($dto, $property) === $value;
        });
    }

    /**
     * Filter DTOs where a property is not equal to a value.
     *
     * @param  string  $property  The property to filter by
     * @param  mixed  $value  The value to exclude
     * @return static
     */
    public function whereNot(string $property, mixed $value): static
    {
        return $this->filter(function ($dto) use ($property, $value) {
            return $this->getPropertyValue($dto, $property) !== $value;
        });
    }

    /**
     * Filter DTOs where a property is null.
     *
     * @param  string  $property  The property to check
     * @return static
     */
    public function whereNull(string $property): static
    {
        return $this->filter(function ($dto) use ($property) {
            return $this->getPropertyValue($dto, $property) === null;
        });
    }

    /**
     * Filter DTOs where a property is not null.
     *
     * @param  string  $property  The property to check
     * @return static
     */
    public function whereNotNull(string $property): static
    {
        return $this->filter(function ($dto) use ($property) {
            return $this->getPropertyValue($dto, $property) !== null;
        });
    }

    /**
     * Filter DTOs where a property is in a list of values.
     *
     * @param  string  $property  The property to check
     * @param  array  $values  The values to match
     * @return static
     */
    public function whereIn(string $property, array $values): static
    {
        return $this->filter(function ($dto) use ($property, $values) {
            return in_array($this->getPropertyValue($dto, $property), $values, true);
        });
    }

    /**
     * Filter DTOs where a property is not in a list of values.
     *
     * @param  string  $property  The property to check
     * @param  array  $values  The values to exclude
     * @return static
     */
    public function whereNotIn(string $property, array $values): static
    {
        return $this->filter(function ($dto) use ($property, $values) {
            return ! in_array($this->getPropertyValue($dto, $property), $values, true);
        });
    }

    /**
     * Sort DTOs by a specific property.
     *
     * @param  string  $property  The property to sort by
     * @param  int  $options  Sort options
     * @param  bool  $descending  Whether to sort in descending order
     * @return static
     */
    public function sortBy(string $property, int $options = SORT_REGULAR, bool $descending = false): static
    {
        return parent::sortBy(function ($dto) use ($property) {
            return $this->getPropertyValue($dto, $property);
        }, $options, $descending);
    }

    /**
     * Sort DTOs by a specific property in descending order.
     *
     * @param  string  $property  The property to sort by
     * @param  int  $options  Sort options
     * @return static
     */
    public function sortByDesc(string $property, int $options = SORT_REGULAR): static
    {
        return $this->sortBy($property, $options, true);
    }

    /**
     * Get a property value from a DTO.
     *
     * @param  mixed  $dto  The DTO object
     * @param  string  $property  The property name
     * @return mixed
     */
    private function getPropertyValue(mixed $dto, string $property): mixed
    {
        // First try to access the property directly
        if (property_exists($dto, $property)) {
            return $dto->{$property};
        }

        // If the DTO has a getProperty method (from DtoUtilities trait), use it
        if (method_exists($dto, 'getProperty')) {
            return $dto->getProperty($property);
        }

        // If the DTO has a toArray method, get the value from there
        if (method_exists($dto, 'toArray')) {
            $array = $dto->toArray();
            return $array[$property] ?? null;
        }

        return null;
    }
}