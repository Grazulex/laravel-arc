<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;

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

        $rules = [$type]; // Laravel accepts 'datetime', 'date', 'time' as validation rules

        if (isset($config['rules']) && is_array($config['rules'])) {
            $rules = array_merge($rules, $config['rules']);
        }

        return [$name => $this->applyRequiredIfNeeded($config, $rules)];
    }
}
