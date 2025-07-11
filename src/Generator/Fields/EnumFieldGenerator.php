<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Fields;

use Grazulex\LaravelArc\Contracts\FieldGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Support\FieldBuilder;

final class EnumFieldGenerator implements FieldGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'enum';
    }

    public function generate(string $name, array $config, DtoGenerationContext $context): string
    {
        // Si une classe enum est spécifiée, utiliser le type enum PHP
        if (isset($config['class']) && is_string($config['class'])) {
            $enumClass = $config['class'];
            $required = $config['required'] ?? true;
            $nullable = $required ? '' : '?';

            // Gérer les valeurs par défaut
            $defaultCode = '';
            if (array_key_exists('default', $config) && $config['default'] !== null) {
                $defaultValue = $config['default'];
                // Si c'est un nom de case, utiliser la syntaxe Enum::CASE
                if (is_string($defaultValue) && ! str_contains($defaultValue, '::')) {
                    $defaultCode = " = \\{$enumClass}::".mb_strtoupper($defaultValue);
                } else {
                    $defaultCode = " = {$defaultValue}";
                }
            } elseif (! $required) {
                $defaultCode = ' = null';
            }

            return "public {$nullable}\\{$enumClass} \${$name}{$defaultCode};";
        }

        // Utiliser le comportement par défaut pour les enums avec valeurs array
        return FieldBuilder::generate($name, 'enum', $config);
    }
}
