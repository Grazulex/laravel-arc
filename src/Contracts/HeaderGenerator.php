<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Contracts;

use Grazulex\LaravelArc\Generator\DtoGenerationContext;

interface HeaderGenerator
{
    public function supports(string $key): bool;

    public function generate(string $name, array $definition, DtoGenerationContext $context): string;
}
