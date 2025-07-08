<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Headers;

use Grazulex\LaravelArc\Contracts\HeaderGenerator;

final class TableHeaderGenerator implements HeaderGenerator
{
    public function supports(string $key): bool
    {
        return $key === 'table';
    }

    public function generate(array $yaml, string $dtoName): ?string
    {
        if (! isset($yaml['table']) || ! is_string($yaml['table'])) {
            return null;
        }

        $table = mb_trim($yaml['table']);

        return "/**\n * Data Transfer Object for table `{$table}`.\n */";
    }
}
