<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

final class IntegerValidatorGenerator extends BaseValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return in_array($type, ['integer', 'int', 'id'], true);
    }

    public function generate(string $name, array $config, DtoGenerationContext $context): array
    {
        $type = $config['type'] ?? null;

        if (! in_array($type, ['integer', 'int', 'id'], true)) {
            return [];
        }

        return [
            $name => ValidatorRuleBuilder::build(['integer'], $config),
        ];
    }
}
