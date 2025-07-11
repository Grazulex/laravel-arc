<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

final class DtoValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'dto';
    }

    public function generate(string $name, array $definition, DtoGenerationContext $context): array
    {
        // Pour un champ DTO, on valide que c'est un array
        $baseRules = ['array'];

        return [
            $name => ValidatorRuleBuilder::build($baseRules, $definition),
        ];
    }
}
