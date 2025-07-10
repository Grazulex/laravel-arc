<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Options;

use Grazulex\LaravelArc\Contracts\FieldExpandingOptionGenerator;

final class TimestampsOptionGenerator implements FieldExpandingOptionGenerator
{
    public function generate(mixed $value): ?string
    {
        return null;
    }

    public function expandFields(mixed $value): array
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return [
                'created_at' => ['type' => 'datetime'],
                'updated_at' => ['type' => 'datetime'],
            ];
        }

        return [];
    }
}
