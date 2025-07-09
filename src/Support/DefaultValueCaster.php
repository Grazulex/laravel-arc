<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support;

final class DefaultValueCaster
{
    public static function cast(string $type, mixed $value): string
    {
        return match ($type) {
            'string', 'uuid' => "'".addslashes((string) $value)."'",
            'date', 'datetime', 'time' => is_null($value) ? 'null' : '',
            'integer' => (string) (int) $value,
            'float' => (string) (float) $value,
            'decimal' => "'".$value."'", // use string for precision
            'boolean' => $value ? 'true' : 'false',
            'array', 'json' => var_export($value, true),
            default => 'null',
        };
    }
}
