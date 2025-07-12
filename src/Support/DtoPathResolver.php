<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support;

use Illuminate\Support\Str;

/**
 * Centralizes logic for resolving paths and namespaces for DTO generation.
 *
 * This class provides bidirectional conversion between namespaces and file paths,
 * making it easier to organize DTOs in custom directory structures.
 */
final class DtoPathResolver
{
    /**
     * Resolves the complete file path based on namespace and DTO name.
     *
     * @param  string  $dtoName  The DTO class name (e.g., "UserDTO")
     * @param  string  $namespace  The complete namespace (e.g., "App\DTO\Admin")
     * @return string The complete file path (e.g., "/path/to/app/DTO/Admin/UserDTO.php")
     */
    public static function resolveOutputPath(string $dtoName, string $namespace): string
    {
        $baseOutputDir = DtoPaths::dtoOutputDir();
        $baseNamespace = DtoPaths::dtoNamespace();

        // If namespace matches exactly the base namespace, no subdirectory needed
        if ($namespace === $baseNamespace) {
            return $baseOutputDir.'/'.$dtoName.'.php';
        }

        // If namespace starts with base namespace, create subdirectories
        if (str_starts_with($namespace, $baseNamespace.'\\')) {
            $relativePath = mb_substr($namespace, mb_strlen($baseNamespace) + 1);
            $subPath = str_replace('\\', '/', $relativePath);

            return $baseOutputDir.'/'.$subPath.'/'.$dtoName.'.php';
        }

        // Completely different namespace - full conversion
        return self::convertNamespaceToPath($namespace, $dtoName);
    }

    /**
     * Derives namespace from a file path.
     *
     * @param  string  $filePath  The complete file path
     * @return string The derived namespace
     */
    public static function resolveNamespaceFromPath(string $filePath): string
    {
        $basePath = base_path();

        // Normalize path separators to forward slashes
        $filePath = str_replace('\\', '/', $filePath);
        $basePath = str_replace('\\', '/', $basePath);

        $relativePath = str_replace($basePath.'/', '', $filePath);

        // Remove the filename
        $dirPath = dirname($relativePath);

        // Handle current directory case
        if ($dirPath === '.') {
            return '';
        }

        return collect(explode('/', $dirPath))
            ->filter()
            ->map(function ($segment) {
                // Preserve existing casing for common abbreviations
                if (mb_strtoupper($segment) === $segment) {
                    return $segment; // Already uppercase
                }
                if (in_array($segment, ['DTOs', 'APIs', 'URLs', 'UUIDs'])) {
                    return $segment; // Preserve known acronyms
                }

                return Str::studly($segment);
            })
            ->implode('\\');
    }

    /**
     * Validates if a namespace is compatible with PHP standards.
     */
    public static function isValidNamespace(string $namespace): bool
    {
        // Basic validations
        if ($namespace === '' || $namespace === '0') {
            return false;
        }

        // Check if namespace follows PHP naming conventions
        if (in_array(preg_match('/^[A-Za-z_][\w\\\\]*\w$/', $namespace), [0, false], true)) {
            return false;
        }

        // Check for consecutive backslashes
        if (str_contains($namespace, '\\\\')) {
            return false;
        }

        // Check each part of the namespace
        $parts = explode('\\', $namespace);
        foreach ($parts as $part) {
            if ($part === '' || $part === '0' || in_array(preg_match('/^[A-Za-z_][\w]*$/', $part), [0, false], true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Normalizes a namespace by trimming whitespace and backslashes.
     */
    public static function normalizeNamespace(string $namespace): string
    {
        return mb_trim(mb_trim($namespace), '\\');
    }

    /**
     * Checks if a namespace is a sub-namespace of another.
     */
    public static function isSubNamespaceOf(string $childNamespace, string $parentNamespace): bool
    {
        $child = self::normalizeNamespace($childNamespace);
        $parent = self::normalizeNamespace($parentNamespace);

        if ($child === $parent) {
            return false; // Same namespace, not a sub-namespace
        }

        return str_starts_with($child, $parent.'\\');
    }

    /**
     * Converts a namespace to a file system path.
     */
    private static function convertNamespaceToPath(string $namespace, string $dtoName): string
    {
        $pathFromNamespace = str_replace('\\', '/', $namespace);

        // Special handling for namespaces starting with "App\"
        if (str_starts_with($pathFromNamespace, 'App/')) {
            $pathFromNamespace = 'app/'.mb_substr($pathFromNamespace, 4);
        }

        return base_path($pathFromNamespace.'/'.$dtoName.'.php');
    }
}
