<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Contracts;

interface RelationGenerator
{
    public function supports(string $type): bool;

    public function generate(string $name, array $definition): string;
}
