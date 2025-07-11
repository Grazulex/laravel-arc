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

            // Validation de base avec la règle enum Laravel (Laravel 9+)
            $rules = ["enum:\\{$enumClass}"];

            // Traiter les règles personnalisées et les séparer des autres
            $customRules = [];
            $otherRules = [];

            if (! empty($config['rules']) && is_array($config['rules'])) {
                foreach ($config['rules'] as $rule) {
                    // Règles personnalisées pour les enums
                    if ($rule === 'enum_exists') {
                        $customRules[] = "enum_exists:\\{$enumClass}";
                    } elseif ($rule === 'in_enum') {
                        $customRules[] = "in_enum:\\{$enumClass}";
                    } else {
                        // Règle standard à traiter par ValidatorRuleBuilder
                        $otherRules[] = $rule;
                    }
                }
            }

            // Ajouter les règles personnalisées directement
            $rules = array_merge($rules, $customRules);

            // Créer une configuration modifiée avec seulement les règles non-personnalisées
            $modifiedConfig = $config;
            $modifiedConfig['rules'] = $otherRules;

            $validationRules = ValidatorRuleBuilder::build($rules, $modifiedConfig);

            return [$name => $validationRules];
        }

        // Comportement par défaut pour les enums avec valeurs array
        $values = $config['values'] ?? null;

        if (! is_array($values) || $values === []) {
            return [];
        }

        $enumRule = 'in:'.implode(',', $values);

        // Pour les enums traditionnels, ignorer les règles personnalisées in_enum et enum_exists
        $modifiedConfig = $config;
        if (! empty($config['rules']) && is_array($config['rules'])) {
            $modifiedConfig['rules'] = array_filter($config['rules'], function ($rule): bool {
                return ! in_array($rule, ['in_enum', 'enum_exists'], true);
            });
        }

        $rules = ValidatorRuleBuilder::build([$enumRule], $modifiedConfig);

        return [$name => $rules];
    }
}
