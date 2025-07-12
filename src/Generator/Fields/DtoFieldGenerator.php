<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Fields;

use Grazulex\LaravelArc\Contracts\FieldGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;

final class DtoFieldGenerator implements FieldGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'dto';
    }

    public function generate(string $name, array $definition, DtoGenerationContext $context): string
    {
        $dtoClass = $definition['dto'] ?? 'mixed';
        $required = $definition['required'] ?? true;
        $nullable = $definition['nullable'] ?? false;

        // Détermine si le champ est nullable
        $nullablePrefix = ($nullable || ! $required) ? '?' : '';

        // Gérer les valeurs par défaut
        $defaultCode = '';
        if (array_key_exists('default', $definition) && $definition['default'] !== null) {
            $defaultCode = " = {$definition['default']}";
        } elseif (! $required) {
            $defaultCode = ' = null';
        }

        // Vérifier si on peut imbriquer ce DTO
        if (! $context->canNestDto($dtoClass)) {
            // Fallback vers un type simple si trop profond ou cyclique
            return "public readonly {$nullablePrefix}array \${$name}{$defaultCode},";
        }

        return "public readonly {$nullablePrefix}\\{$dtoClass} \${$name}{$defaultCode},";
    }
}
