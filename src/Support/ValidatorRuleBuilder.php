<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support;

final class ValidatorRuleBuilder
{
    /**
     * @param  string[]  $defaultRules
     * @param  array  $definition  YAML field definition (with optional 'rules', 'validation' and 'required')
     * @return string[]
     */
    public static function build(array $defaultRules, array $definition): array
    {
        $rules = $defaultRules;

        // Handle required/nullable logic
        $required = $definition['required'] ?? true;

        if ($required && ! in_array('required', $rules, true)) {
            $rules[] = 'required';
        } elseif (! $required && ! in_array('nullable', $rules, true)) {
            $rules[] = 'nullable';
        }

        // Merge user-defined rules from 'validation' field (YAML format)
        $validationRules = $definition['validation'] ?? [];
        if (! empty($validationRules) && is_array($validationRules)) {
            foreach ($validationRules as $rule) {
                if (! in_array($rule, $rules, true)) {
                    $rules[] = $rule;
                }
            }
        }

        // Merge user-defined rules from 'rules' field (legacy support)
        $legacyRules = $definition['rules'] ?? [];
        if (! empty($legacyRules) && is_array($legacyRules)) {
            foreach ($legacyRules as $rule) {
                if (! in_array($rule, $rules, true)) {
                    $rules[] = $rule;
                }
            }
        }

        return $rules;
    }
}
