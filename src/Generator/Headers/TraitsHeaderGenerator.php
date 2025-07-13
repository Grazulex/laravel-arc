<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Headers;

use Grazulex\LaravelArc\Contracts\HeaderGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;

final class TraitsHeaderGenerator implements HeaderGenerator
{
    public function supports(string $key): bool
    {
        return $key === 'traits';
    }

    public function generate(string $key, array $header, DtoGenerationContext $context): string
    {
        $traits = $header['traits'] ?? [];

        if (empty($traits)) {
            return '';
        }

        $useStatements = [];

        foreach ($traits as $traitName) {
            // Add use statement for the trait itself
            $useStatements[] = "use Grazulex\\LaravelArc\\Support\\Traits\\Behavioral\\{$traitName};";

            // Résoudre le trait complet via le registry
            if (BehavioralTraitRegistry::resolveTrait($traitName)) {
                $traitInfoClass = BehavioralTraitRegistry::getTraitInfo($traitName);
                $behavioralUseStatements = $traitInfoClass::getTraitUseStatements();
                foreach ($behavioralUseStatements as $useStatement) {
                    $useStatements[] = "use {$useStatement};";
                }
            }
        }

        return implode("\n", array_unique($useStatements));
    }
}
