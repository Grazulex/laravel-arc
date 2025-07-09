<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Relations;

use Grazulex\LaravelArc\Contracts\RelationGenerator;
use Grazulex\LaravelArc\Support\DtoNamespaceResolver;

final class HasManyRelationGenerator implements RelationGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'hasMany';
    }

    public function generate(string $name, array $definition): string
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
