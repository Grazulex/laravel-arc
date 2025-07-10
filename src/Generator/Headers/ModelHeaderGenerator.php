<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Headers;

use Grazulex\LaravelArc\Contracts\HeaderGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;

final class ModelHeaderGenerator implements HeaderGenerator
{
    public function supports(string $key): bool
    {
        return $key === 'model';
    }

    public function generate(string $key, array $header, DtoGenerationContext $context): string
    {
        $model = $header[$key] ?? 'App\\Models\\Model';

        if (! is_string($model)) {
            return '\\App\\Models\\Model';
        }

        return '\\'.ltrim($model, '\\');
    }
}
