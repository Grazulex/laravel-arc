<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

final class DateTimeValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return in_array($type, ['datetime', 'date', 'time'], true);
    }

    public function generate(string $name, array $definition): array
    {
        $rules = ValidatorRuleBuilder::build(['date'], $definition);

        return [$name => $rules];
    }
}
