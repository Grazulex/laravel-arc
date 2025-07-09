<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support;

final class DefaultValueCaster
{
    public static function cast(string $type, mixed $value): string
    {
        return match ($type) {
            // strings
            'string', 'text', 'uuid', 'enum', 'id' => "'".addslashes((string) $value)."'",

            // dates
            'date', 'datetime', 'time' => is_null($value) ? 'null' : '',

            // numbers
            'integer', 'bigint' => (string) (int) $value,
            'float' => (string) (float) $value,
            'decimal' => "'".$value."'", // force string for precision

            // booleans
            'boolean' => $value ? 'true' : 'false',

            // structures
            'array', 'json' => var_export($value, true),

            default => 'null',
        };
    }
}
