<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

final class FloatValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'float';
    }

    public function generate(string $name, array $definition): array
    {
        $rules = ValidatorRuleBuilder::build(['numeric'], $definition);

        return [$name => $rules];
    }
}
