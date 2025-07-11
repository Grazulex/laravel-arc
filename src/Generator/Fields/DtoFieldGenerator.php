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
        $nullable = $required ? '' : '?';

        // Vérifier si on peut imbriquer ce DTO
        if (! $context->canNestDto($dtoClass)) {
            // Fallback vers un type simple si trop profond ou cyclique
            return "public readonly {$nullable}array \${$name},";
        }

        return "public readonly {$nullable}\\{$dtoClass} \${$name},";
    }
}
