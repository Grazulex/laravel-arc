<?php

namespace Grazulex\LaravelArc\Support;

class DefaultValueCaster
{
    public static function cast(string $type, mixed $value): string
    {
        return match ($type) {
            'string', 'uuid' => "'" . addslashes((string) $value) . "'",
            'integer'        => (string) intval($value),
            'float'          => (string) floatval($value),
            'decimal'        => "'" . (string) $value . "'", // use string for precision
            'boolean'        => $value ? 'true' : 'false',
            'array', 'json'  => var_export($value, true),
            default          => 'null',
        };
    }
}
