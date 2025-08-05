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
        // Priority: model_fqcn > model > default
        $model = $header['model_fqcn'] ?? $header['model'] ?? 'App\\Models\\Model';

        if (! is_string($model)) {
            return '\\App\\Models\\Model';
        }

        // Debug: Log the model being processed
        // error_log("ModelHeaderGenerator processing model: " . $model);

        // Ensure proper namespace formatting
        $model = str_replace('/', '\\', $model); // Convert any forward slashes
        $model = '\\'.mb_ltrim($model, '\\');    // Ensure leading backslash

        return $model;
    }
}
