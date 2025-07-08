<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Contracts;

interface HeaderGeneratorContract
{
    /**
     * Determine if the generator supports the given header key.
     */
    public function supports(string $key): bool;

    /**
     * Generate PHP code or metadata from the given header section.
     *
     * @param  array  $yaml  Entire YAML structure
     * @param  string  $dtoName  Name of the DTO class
     * @return string PHP code or relevant content
     */
    public function generate(array $yaml, string $dtoName): string;
}
