<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

final class StringValidatorGenerator extends BaseValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'string';
    }

    public function generate(string $name, array $config, DtoGenerationContext $context): array
    {
        if (! $this->isMatchingType($config, 'string')) {
            return [];
        }

        return [
            $name => ValidatorRuleBuilder::build(['string'], $config),
        ];
    }
}
