<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support;

final class FieldTypeResolver
{
    public static function resolvePhpType(string $baseType, bool $required = true): string
    {
        return ($required ? '' : '?').match ($baseType) {
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

            // DTOs imbriqués - traité spécialement dans le générateur
            'dto' => 'mixed',

            default => 'mixed',
        };
    }
}
