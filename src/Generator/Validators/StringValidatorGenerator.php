<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

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

        $rules = ValidatorRuleBuilder::build(['string'], $config);

        return [$name => $rules];
    }
}
