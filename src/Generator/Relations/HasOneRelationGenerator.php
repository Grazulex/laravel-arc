<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Relations;

use Grazulex\LaravelArc\Contracts\RelationGenerator;

final class HasOneRelationGenerator implements RelationGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'hasOne';
    }

    public function generate(string $name, array $definition): string
    {
        $dtoClass = $definition['dto'] ?? 'UNKNOWN_DTO';

        return <<<PHP
    public {$dtoClass} \$$name;
PHP;
    }
}
