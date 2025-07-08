<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Headers;

use Grazulex\LaravelArc\Contracts\HeaderGenerator;

final class ModelHeaderGenerator implements HeaderGenerator
{
    public function supports(string $key): bool
    {
        return $key === 'model';
    }

    public function generate(array $yaml, string $dtoName): ?string
    {
        if (! isset($yaml['model']) || ! is_string($yaml['model'])) {
            return null;
        }

        $modelClass = mb_trim($yaml['model'], '\\');

        return "use {$modelClass};";
    }
}
