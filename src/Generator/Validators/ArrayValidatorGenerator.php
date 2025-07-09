<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

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

        $rules = ValidatorRuleBuilder::build(['array'], $config);

        return [$name => $rules];
    }
}
