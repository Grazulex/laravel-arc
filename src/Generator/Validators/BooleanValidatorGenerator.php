<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

final class BooleanValidatorGenerator extends BaseValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'boolean';
    }

    public function generate(string $name, array $config, DtoGenerationContext $context): array
    {
        if (! $this->isMatchingType($config, 'boolean')) {
            return [];
        }

        $rules = ValidatorRuleBuilder::build(['boolean'], $config);

        return [$name => $rules];
    }
}
