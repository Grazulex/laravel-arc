<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

final class ArrayValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return in_array($type, ['array', 'json'], true);
    }

    public function generate(string $name, array $definition): array
    {
        $rules = ValidatorRuleBuilder::build(['array'], $definition);

        return [$name => $rules];
    }
}
