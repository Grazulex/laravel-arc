<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support;

use Illuminate\Support\Collection;

/**
 * Specialized collection for DTOs that provides additional functionality
 * similar to Laravel API Resources Collections.
 */
final class DtoCollection extends Collection
{
    /**
     * Convert the collection to JSON in API resource format.
     *
     * @param  array  $meta  Additional meta information
     * @return string JSON representation
     */
    public function toJsonResource(array $meta = []): string
    {
        $data = [
            'data' => $this->map(fn ($dto) => $dto->toArray())->toArray(),
        ];

        if ($meta !== []) {
            $data['meta'] = $meta;
        }

        return json_encode($data);
    }

    /**
     * Convert the collection to array in API resource format.
     *
     * @param  array  $meta  Additional meta information
     * @return array Array representation
     */
    public function toArrayResource(array $meta = []): array
    {
        $data = [
            'data' => $this->map(fn ($dto) => $dto->toArray())->toArray(),
        ];

        if ($meta !== []) {
            $data['meta'] = $meta;
        }

        return $data;
    }

    /**
     * Filter DTOs by specific field values.
     *
     * @param  string  $field  The field to filter by
     * @param  mixed  $value  The value to match
     * @return static Filtered collection
     */
    public function whereField(string $field, mixed $value): static
    {
        return $this->filter(function ($dto) use ($field, $value): bool {
            return property_exists($dto, $field) && $value === $dto->$field;
        });
    }

    /**
     * Paginate the DTO collection.
     *
     * @param  int  $perPage  Items per page
     * @param  int  $page  Current page
     * @param  string  $pageName  Page parameter name
     * @return array Paginated result with meta
     */
    public function paginate(int $perPage = 15, int $page = 1, string $pageName = 'page'): array
    {
        $total = $this->count();
        $offset = ($page - 1) * $perPage;
        $items = $this->slice($offset, $perPage);

        return [
            'data' => $items->map(fn ($dto) => $dto->toArray())->toArray(),
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total),
                'has_more_pages' => $page < ceil($total / $perPage),
            ],
        ];
    }

    /**
     * Group DTOs by a specific field.
     *
     * @param  string  $field  The field to group by
     * @return Collection<string, static> Grouped collection
     */
    public function groupByField(string $field): Collection
    {
        return $this->groupBy(function ($dto) use ($field) {
            return $dto->hasProperty($field) ? $dto->getProperty($field) : null;
        })->map(fn ($group): DtoCollection => new self($group));
    }

    /**
     * Get only specific fields from all DTOs.
     *
     * @param  array  $fields  Fields to include
     * @return Collection<int, array> Collection of arrays with selected fields
     */
    public function onlyFields(array $fields): Collection
    {
        return $this->map(fn ($dto) => $dto->only($fields));
    }

    /**
     * Exclude specific fields from all DTOs.
     *
     * @param  array  $fields  Fields to exclude
     * @return Collection<int, array> Collection of arrays without excluded fields
     */
    public function exceptFields(array $fields): Collection
    {
        return $this->map(fn ($dto) => $dto->except($fields));
    }
}
