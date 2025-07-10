<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support;

final class FieldBuilder
{
    public static function generate(string $name, string $type, array $config): string
    {
        $required = $config['required'] ?? true;
        $defaultDefined = array_key_exists('default', $config);
        $default = $config['default'] ?? null;

        $phpType = FieldTypeResolver::resolvePhpType($type, $required);

        if ($defaultDefined) {
            $casted = DefaultValueCaster::cast($type, $default);
            $defaultCode = $casted !== '' ? ' = '.$casted : '';
        } elseif (! $required) {
            $defaultCode = ' = null';
        } else {
            $defaultCode = '';
        }

        return "public {$phpType} \${$name}{$defaultCode};";
    }
}
