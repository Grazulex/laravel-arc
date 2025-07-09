<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Relations;

use Grazulex\LaravelArc\Contracts\RelationGenerator;
use Grazulex\LaravelArc\Support\DtoNamespaceResolver;

final class BelongsToRelationGenerator implements RelationGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'belongsTo';
    }

    public function generate(string $name, array $definition): string
    {
        $dto = $definition['dto'] ?? null;
        $dto = $dto ?: 'UNKNOWN';

        $dtoClass = DtoNamespaceResolver::resolveDtoClass($dto);

        return <<<PHP
    public {$dtoClass} \$$name;
PHP;
    }
}
