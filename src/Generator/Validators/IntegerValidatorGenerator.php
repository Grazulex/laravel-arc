<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

final class IntegerValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'integer';
    }

    public function generate(string $name, array $definition): array
    {
        $rules = ValidatorRuleBuilder::build(['integer'], $definition);

        return [$name => $rules];
    }
}
