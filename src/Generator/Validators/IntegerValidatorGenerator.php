<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

final class IntegerValidatorGenerator extends BaseValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'integer';
    }

    public function generate(string $name, array $config): array
    {
        if (! $this->isMatchingType($config, 'integer')) {
            return [];
        }

        return [
            $name => ValidatorRuleBuilder::build(['integer'], $config),
        ];
    }
}
