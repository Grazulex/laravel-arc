<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

final class EnumValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'enum';
    }

    public function generate(string $name, array $definition): array
    {
        $rules = [];

        if (! empty($definition['values']) && is_array($definition['values'])) {
            $rules[] = 'in:'.implode(',', $definition['values']);
        }

        return [$name => ValidatorRuleBuilder::build($rules, $definition)];
    }
}
