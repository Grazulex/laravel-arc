<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Headers;

use Grazulex\LaravelArc\Contracts\HeaderGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;

final class UseHeaderGenerator implements HeaderGenerator
{
    public function supports(string $key): bool
    {
        return $key === 'use';
    }

    public function generate(string $key, array $header, DtoGenerationContext $context): string
    {
        $useStatements = $header[$key] ?? [];

        if (empty($useStatements)) {
            return '';
        }

        // Handle both array and string formats
        if (is_string($useStatements)) {
            $useStatements = [$useStatements];
        }

        $statements = [];
        foreach ($useStatements as $useStatement) {
            if (is_string($useStatement) && ! in_array(mb_trim($useStatement), ['', '0'], true)) {
                $statements[] = 'use '.mb_trim($useStatement, ' ;').';';
            }
        }

        return implode("\n", $statements);
    }
}
