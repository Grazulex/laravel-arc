<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Contracts;

interface ValidatorGenerator
{
    public function supports(string $type): bool;

    /**
     * @return array<string, array<string>> Laravel-style validation rules
     */
    public function generate(string $name, array $config): array;
}
