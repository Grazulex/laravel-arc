<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

final class EnumValidatorGenerator extends BaseValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'enum';
    }

    public function generate(string $name, array $config): array
    {
        if (! $this->isMatchingType($config, 'enum') || ! isset($config['values']) || ! is_array($config['values'])) {
            return [];
        }

        $enumRule = 'in:'.implode(',', $config['values']);

        $rules = ValidatorRuleBuilder::build([$enumRule], $config);

        return [$name => $rules];
    }
}
