<?php

declare(strict_types=1);

namespace App\DTOs;

use Grazulex\LaravelArc\Support\Traits\Behavioral\HasSoftDeletes;
use Grazulex\LaravelArc\Support\Traits\Behavioral\HasTimestamps;
use Grazulex\LaravelArc\Support\Traits\Behavioral\HasUuid;
use Grazulex\LaravelArc\Support\Traits\ConvertsData;
use Grazulex\LaravelArc\Support\Traits\DtoUtilities;
use Grazulex\LaravelArc\Support\Traits\ValidatesData;

/**
 * Example DTO generated with behavioral traits.
 *
 * This is what a generated DTO would look like with the new trait system.
 */
final readonly class UserDto
{
    // Functional traits (existing system)
    use ConvertsData;
    use DtoUtilities;
    // Behavioral traits (new system)
    use HasSoftDeletes;

    use HasTimestamps;
    use HasUuid;
    use ValidatesData;

    public function __construct(
        // Regular fields defined in YAML
        public string $name,
        public string $email,
        public ?string $slug = null,
        public ?int $age = null,

        // Fields added by traits would be initialized here
        // But since traits add properties, they're handled in the trait itself
    ) {}

    /**
     * Create DTO from model.
     */
    public static function fromModel($model): self
    {
        return new self(
            name: $model->name,
            email: $model->email,
            slug: $model->slug,
            age: $model->age,
        );
    }

    /**
     * Validation rules (merged with trait rules).
     */
    public static function rules(): array
    {
        return [
            // Regular field rules
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'slug' => ['nullable', 'string'],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],

            // Trait rules would be merged automatically
            'deleted_at' => ['nullable', 'date'],
            'created_at' => ['nullable', 'date'],
            'updated_at' => ['nullable', 'date'],
            'uuid' => ['nullable', 'uuid'],
        ];
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'slug' => $this->slug,
            'age' => $this->age,

            // Trait fields would be included automatically
            'deleted_at' => $this->deleted_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'uuid' => $this->uuid,
        ];
    }

    /**
     * Create a new instance with modified properties (immutable update).
     */
    public function with(array $attributes): static
    {
        $values = $this->toArray();
        $merged = array_merge($values, $attributes);

        return new self(
            name: $merged['name'],
            email: $merged['email'],
            slug: $merged['slug'],
            age: $merged['age'],
        );
    }
}
