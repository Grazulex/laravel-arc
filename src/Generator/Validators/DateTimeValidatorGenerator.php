<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

final class DateTimeValidatorGenerator extends BaseValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return in_array($type, ['datetime', 'date', 'time'], true);
    }

    public function generate(string $name, array $config): array
    {
        $type = $config['type'] ?? null;

        if (! in_array($type, ['datetime', 'date', 'time'], true)) {
            return [];
        }

        $rules = ValidatorRuleBuilder::build([$type], $config);

        return [$name => $rules];
    }
}
