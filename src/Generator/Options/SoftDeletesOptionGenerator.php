<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Options;

use Grazulex\LaravelArc\Contracts\FieldExpandingOptionGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;

final class SoftDeletesOptionGenerator implements FieldExpandingOptionGenerator
{
    public function generate(string $name, mixed $value, DtoGenerationContext $context): string
    {
        return ''; // aucun code à générer ici
    }

    public function expandFields(mixed $value): array
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return [
                'deleted_at' => ['type' => 'datetime', 'required' => false],
            ];
        }

        return [];
    }
}
