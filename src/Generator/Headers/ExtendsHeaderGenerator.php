<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Headers;

use Grazulex\LaravelArc\Contracts\HeaderGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;

final class ExtendsHeaderGenerator implements HeaderGenerator
{
    public function supports(string $key): bool
    {
        return $key === 'extends';
    }

    public function generate(string $key, array $header, DtoGenerationContext $context): string
    {
        $extends = $header[$key] ?? null;

        if (empty($extends) || ! is_string($extends)) {
            return '';
        }

        $extends = mb_trim($extends);
        if ($extends === '' || $extends === '0') {
            return '';
        }

        // Ensure we have a proper class name
        return 'extends '.$extends;
    }
}
