<?php

declare(strict_types=1);

namespace Grazulex\Arc\Services\Commands\Generation\TypeDetectors;

use function is_string;

final class PatternTypeDetector implements TypeDetectorInterface
{
    public function detect(mixed $value): string
    {
        if (is_string($value)) {
            return match (true) {
                str_contains($value, '_at') => '\\DateTimeInterface',
                str_contains($value, '_date') => '\\DateTimeInterface',
                str_contains($value, '_timestamp') => 'int',
                str_contains($value, '_count') => 'int',
                str_contains($value, '_id') => 'int',
                str_contains($value, 'is_') => 'bool',
                str_contains($value, 'has_') => 'bool',
                default => 'string'
            };
        }

        return 'mixed';
    }
}
