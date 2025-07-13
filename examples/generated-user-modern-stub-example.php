<?php

declare(strict_types=1);

namespace App\DTO;

use Grazulex\LaravelArc\Support\Traits\Behavioral\HasSoftDeletes;
use Grazulex\LaravelArc\Support\Traits\Behavioral\HasTimestamps;
use Grazulex\LaravelArc\Support\Traits\Behavioral\HasUuid;
use Grazulex\LaravelArc\Support\Traits\ConvertsData;
use Grazulex\LaravelArc\Support\Traits\DtoUtilities;
use Grazulex\LaravelArc\Support\Traits\ValidatesData;

/**
 * Generated DTO using Modern Trait-Based Architecture
 *
 * This DTO always includes the 3 functional traits:
 * - ValidatesData: Provides validation capabilities
 * - ConvertsData: Provides data conversion and casting
 * - DtoUtilities: Provides utility methods (toArray, toJson, etc.)
 *
 * Additional behavioral traits are included as declared in YAML.
 * By default, this DTO does NOT extend any base class.
 */
final class User
{
    use ConvertsData;
    use DtoUtilities;
    use HasSoftDeletes;
    use HasTimestamps;
    use HasUuid;
    use ValidatesData;

    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly ?int $age = null,
        public readonly ?object $profile = null,
    ) {}

    public static function fromModel(App\Models\User $model): self
    {
        return new self(
            name: $model->name,
            email: $model->email,
            age: $model->age,
            profile: $model->profile,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'age' => $this->age,
            'profile' => $this->profile,
        ];
    }

    // Relation methods would be generated here if needed
    // Note: Fields like id, created_at, updated_at, deleted_at are automatically handled by behavioral traits
}
