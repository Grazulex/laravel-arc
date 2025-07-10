<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Headers;

use Grazulex\LaravelArc\Contracts\HeaderGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;

final class TableHeaderGenerator implements HeaderGenerator
{
    public function supports(string $key): bool
    {
        return $key === 'table';
    }

    public function generate(string $key, array $header, DtoGenerationContext $context): string
    {
        return $header[$key] ?? 'undefined_table';
    }
}
