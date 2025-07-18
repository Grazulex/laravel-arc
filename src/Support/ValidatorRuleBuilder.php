<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support;

final class ValidatorRuleBuilder
{
    /**
     * @param  string[]  $defaultRules
     * @param  array  $definition  YAML field definition (with optional 'rules' and 'required')
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

        // Merge user-defined rules
        if (! empty($definition['rules']) && is_array($definition['rules'])) {
            foreach ($definition['rules'] as $rule) {
                if (! in_array($rule, $rules, true)) {
                    $rules[] = $rule;
                }
            }
        }

        return $rules;
    }
}
