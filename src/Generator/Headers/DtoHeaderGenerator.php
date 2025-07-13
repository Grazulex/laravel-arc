<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Headers;

use Grazulex\LaravelArc\Contracts\HeaderGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;

final class DtoHeaderGenerator implements HeaderGenerator
{
    public function supports(string $key): bool
    {
        return $key === 'dto';
    }

    public function generate(string $key, array $header, DtoGenerationContext $context): string
    {
        return $header['class'] ?? $header['dto'] ?? 'UnnamedDto';
    }
}
