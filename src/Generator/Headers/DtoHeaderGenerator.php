<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Headers;

use Grazulex\LaravelArc\Contracts\HeaderGenerator;

final class DtoHeaderGenerator implements HeaderGenerator
{
    public function supports(string $key): bool
    {
        return $key === 'dto';
    }

    public function generate(array $yaml, string $dtoName): string
    {
        $className = $yaml['dto'] ?? $dtoName;

        return "final readonly class {$className}\n{\n";
    }
}
