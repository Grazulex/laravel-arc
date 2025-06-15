<?php

declare(strict_types=1);

namespace Grazulex\Arc\Services\Commands\Generation\TypeDetectors;

use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;

final class CastTypeDetector implements TypeDetectorInterface
{
    public function detect(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'bool',
            is_int($value) => 'int',
            is_float($value) => 'float',
            is_string($value) => 'string',
            is_array($value) => 'array',
            default => 'mixed'
        };
    }
}
