<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support;

use Illuminate\Support\Str;

final class DtoPaths
{
    /**
     * Get the directory where YAML DTO definitions are stored.
     */
    public static function definitionDir(): string
    {
        return (string) config('dto.definitions_path', base_path('database/dto_definitions'));
    }

    /**
     * Get the directory where generated PHP DTO classes should be written.
     */
    public static function dtoOutputDir(): string
    {
        return (string) config('dto.output_path', base_path('app/DTO'));
    }

    /**
     * Get the full path of a DTO PHP file (e.g. UserDTO.php).
     */
    public static function dtoFilePath(string $dtoName): string
    {
        return mb_rtrim(self::dtoOutputDir(), '/').'/'.$dtoName.'.php';
    }

    /**
     * Get the full path of the YAML definition file (e.g. user.yaml).
     */
    public static function definitionFilePath(string $dtoName): string
    {
        $filename = mb_strtolower(str_replace('DTO', '', $dtoName)).'.yaml';

        return mb_rtrim(self::definitionDir(), '/').'/'.$filename;
    }

    /**
     * Get the namespace that matches the DTO output path.
     *
     * If a manual namespace is configured, it takes precedence.
     * Otherwise, it's automatically derived from the output path.
     */
    public static function dtoNamespace(): string
    {
        if ($manual = config('dto.namespace')) {
            return mb_trim($manual, '\\');
        }

        $outputDir = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, self::dtoOutputDir());
        $basePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, base_path());

        $relativePath = str_replace($basePath.DIRECTORY_SEPARATOR, '', $outputDir);

        return collect(explode(DIRECTORY_SEPARATOR, $relativePath))
            ->filter()
            ->map(fn ($segment) => Str::studly($segment))
            ->implode('\\');
    }
}
