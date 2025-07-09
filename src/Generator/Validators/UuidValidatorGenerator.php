<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

final class UuidValidatorGenerator extends BaseValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'uuid';
    }

    public function generate(string $name, array $config): array
    {
        if (! $this->isMatchingType($config, 'uuid')) {
            return [];
        }

        $rules = ValidatorRuleBuilder::build(['uuid'], $config);

        return [$name => $rules];
    }
}
