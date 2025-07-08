<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Contracts;

interface HeaderGenerator
{
    public function supports(string $key): bool;

    public function generate(array $yaml, string $dtoName): ?string;
}
