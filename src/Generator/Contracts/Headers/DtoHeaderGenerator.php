<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Headers;

use Grazulex\LaravelArc\Generator\Contracts\HeaderGeneratorContract;

final class DtoHeaderGenerator implements HeaderGeneratorContract
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
