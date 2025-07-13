<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Options;

use Grazulex\LaravelArc\Contracts\FieldExpandingOptionGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;

final class UuidOptionGenerator implements FieldExpandingOptionGenerator
{
    public function generate(string $name, mixed $value, DtoGenerationContext $context): string
    {
        if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return '';
        }

        return <<<'PHP'
    /**
     * Generate a new UUID for this DTO
     */
    public static function generateUuid(): string
    {
        return (string) \Illuminate\Support\Str::uuid();
    }

    /**
     * Create a new instance with generated UUID
     */
    public static function withGeneratedUuid(array $data = []): static
    {
        return new static(
            id: self::generateUuid(),
            ...array_filter($data, fn($key) => $key !== 'id', ARRAY_FILTER_USE_KEY)
        );
    }
PHP;
    }

    public function expandFields(mixed $value): array
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return [
                'id' => [
                    'type' => 'uuid',
                    'required' => true,
                    'rules' => ['uuid'],
                ],
            ];
        }

        return [];
    }
}
