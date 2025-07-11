<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

final class EnumValidatorGenerator extends BaseValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'enum';
    }

    public function generate(string $name, array $config, DtoGenerationContext $context): array
    {
        if (! $this->isMatchingType($config, 'enum')) {
            return [];
        }

        // Si une classe enum est spécifiée, utiliser la validation enum Laravel
        if (isset($config['class']) && is_string($config['class'])) {
            $enumClass = $config['class'];
            $enumRule = "enum:\\{$enumClass}";

            $rules = ValidatorRuleBuilder::build([$enumRule], $config);

            return [$name => $rules];
        }

        // Comportement par défaut pour les enums avec valeurs array
        $values = $config['values'] ?? null;

        if (! is_array($values) || $values === []) {
            return [];
        }

        $enumRule = 'in:'.implode(',', $values);

        $rules = ValidatorRuleBuilder::build([$enumRule], $config);

        return [$name => $rules];
    }
}
