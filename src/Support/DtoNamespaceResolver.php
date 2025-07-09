<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support;

final class DtoNamespaceResolver
{
    public static function resolveDtoClass(string $dto): string
    {
        if (str_contains($dto, '\\')) {
            // FQCN déjà fourni
            return $dto;
        }

        $namespace = config('dto.dto_namespace', 'App\\DTO');

        return mb_rtrim($namespace, '\\').'\\'.$dto;
    }
}
