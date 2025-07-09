<?php

namespace Grazulex\LaravelArc\Support;

class FieldTypeResolver
{
    public static function resolvePhpType(string $baseType, bool $nullable = false): string
    {
        return ($nullable ? '?' : '') . match ($baseType) {
            'string'   => 'string',
            'integer'  => 'int',
            'float'    => 'float',
            'decimal'  => 'string', // for precision (e.g. money)
            'boolean'  => 'bool',
            'array',
            'json'     => 'array',
            'datetime',
            'date',
            'time'     => '\Carbon\Carbon',
            'uuid'     => 'string',
            default    => 'mixed',
        };
    }
}
