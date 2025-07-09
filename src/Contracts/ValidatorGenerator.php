<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Contracts;

interface ValidatorGenerator
{
    public function supports(string $type): bool;

    public function generate(string $name, array $config): ?string;
}
