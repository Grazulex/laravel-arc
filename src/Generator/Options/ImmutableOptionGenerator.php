<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Options;

use Grazulex\LaravelArc\Contracts\OptionGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;

final class ImmutableOptionGenerator implements OptionGenerator
{
    public function generate(string $name, mixed $value, DtoGenerationContext $context): string
    {
        if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return '';
        }

        return <<<'PHP'
    /**
     * Create a new instance with modified data
     */
    public function with(array $changes): static
    {
        $currentData = get_object_vars($this);
        $newData = array_merge($currentData, $changes);
        
        return new static(...$newData);
    }

    /**
     * Create a copy of this DTO
     */
    public function copy(): static
    {
        return new static(...get_object_vars($this));
    }

    /**
     * Compare with another DTO
     */
    public function equals(self $other): bool
    {
        return get_object_vars($this) === get_object_vars($other);
    }

    /**
     * Get a hash of this DTO (useful for caching)
     */
    public function hash(): string
    {
        return hash('sha256', serialize(get_object_vars($this)));
    }
PHP;
    }
}
