<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;

final class StringValidatorGenerator extends BaseValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'string';
    }

    public function generate(string $name, array $config): array
    {
        if (! $this->isMatchingType($config, 'string')) {
            return [];
        }

        $rules = ['string'];

        if (isset($config['rules']) && is_array($config['rules'])) {
            $rules = array_merge($rules, $config['rules']);
        }

        $rules = $this->applyRequiredIfNeeded($config, $rules);

        return [$name => $rules];
    }
}
