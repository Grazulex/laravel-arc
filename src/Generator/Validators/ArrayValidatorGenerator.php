<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;

final class ArrayValidatorGenerator extends BaseValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'array';
    }

    public function generate(string $name, array $config): array
    {
        if (! $this->isMatchingType($config, 'array')) {
            return [];
        }

        $rules = ['array'];

        if (isset($config['rules']) && is_array($config['rules'])) {
            $rules = array_merge($rules, $config['rules']);
        }

        return [$name => $this->applyRequiredIfNeeded($config, $rules)];
    }
}
