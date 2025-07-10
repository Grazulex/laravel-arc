<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Relations;

use Grazulex\LaravelArc\Contracts\RelationGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Support\DtoNamespaceResolver;

final class BelongsToManyRelationGenerator implements RelationGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'belongsToMany';
    }

    public function generate(string $name, array $definition, DtoGenerationContext $context): string
    {
        $dto = $definition['dto'] ?? null;
        $dto = $dto ?: 'UNKNOWN';
        $dtoClass = DtoNamespaceResolver::resolveDtoClass($dto);

        return <<<PHP
    /** @var {$dtoClass}[] */
    public array \$$name;
PHP;
    }
}
