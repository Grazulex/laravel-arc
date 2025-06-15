<?php

declare(strict_types=1);

namespace Grazulex\Arc\Services\Commands\Generation\TypeDetectors;

use function is_string;

final class DatabaseTypeDetector implements TypeDetectorInterface
{
    public function detect(mixed $value): string
    {
        if (is_string($value)) {
            return match ($value) {
                'integer' => 'int',
                'boolean' => 'bool',
                'double', 'decimal', 'float' => 'float',
                'array', 'json' => 'array',
                default => 'string'
            };
        }

        return 'mixed';
    }
}
