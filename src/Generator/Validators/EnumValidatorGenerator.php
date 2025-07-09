<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;

final class EnumValidatorGenerator extends BaseValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'enum';
    }

    public function generate(string $name, array $config): array
    {
        if (! $this->isMatchingType($config, 'enum')) {
            return [];
        }

        if (! isset($config['values']) || ! is_array($config['values'])) {
            return [];
        }

        $rules = ['in:'.implode(',', $config['values'])];

        return [$name => $this->applyRequiredIfNeeded($config, $rules)];
    }
}
