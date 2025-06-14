<?php

namespace Grazulex\Arc\Examples;

use Carbon\Carbon;
use Grazulex\Arc\Attributes\DateProperty;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;

/**
 * Example DTO created with:
 * php artisan make:dto AdvancedUser --model=AdvancedUser
 *
 * This demonstrates how the command intelligently detects types from:
 * - Model casts (highest priority)
 * - Database schema inspection
 * - Migration file analysis
 * - Naming pattern fallbacks
 *
 * Original Model had these casts:
 * protected $casts = [
 *     'age' => 'integer',           // Detected as non-nullable int
 *     'is_active' => 'boolean',     // Detected as non-nullable bool
 *     'metadata' => 'array',        // Detected as nullable array
 *     'salary' => 'decimal:2',      // Detected as non-nullable float
 *     'created_at' => 'datetime',   // Detected as nullable Carbon with DateProperty
 * ];
 */
class AdvancedModelDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: false)]
    public ?string $name;

    #[Property(type: 'string', required: false, validation: 'email')]
    public ?string $email;

    // Cast: 'integer' → Non-nullable int
    #[Property(type: 'int', required: true)]
    public int $age;

    // Cast: 'boolean' → Non-nullable bool
    #[Property(type: 'bool', required: true)]
    public bool $is_active;

    // Cast: 'array' → Nullable array (JSON in database)
    #[Property(type: 'array', required: false)]
    public ?array $metadata;

    // Cast: 'decimal:2' → Non-nullable float
    #[Property(type: 'float', required: true)]
    public float $salary;

    // Cast: 'datetime' → Nullable Carbon with DateProperty
    #[DateProperty(required: false)]
    public ?Carbon $created_at;

    #[DateProperty(required: false)]
    public ?Carbon $updated_at;
}
