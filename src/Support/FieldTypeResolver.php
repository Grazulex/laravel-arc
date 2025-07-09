<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support;

final class FieldTypeResolver
{
    public static function resolvePhpType(string $baseType, bool $nullable = false): string
    {
        return ($nullable ? '?' : '').match ($baseType) {
            // chaînes
            'string', 'text', 'uuid', 'enum', 'id' => 'string',

            // nombres
            'integer', 'bigint' => 'int',
            'float' => 'float',
            'decimal' => 'string', // sécurité / précision (ex: money)

            // booléens
            'boolean' => 'bool',

            // structures
            'array', 'json' => 'array',

            // objets date
            'datetime', 'date', 'time' => '\Carbon\Carbon',

            default => 'mixed',
        };
    }
}
