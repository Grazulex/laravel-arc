<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

abstract class BaseValidatorGenerator
{
    /**
     * Vérifie si le type défini dans la config correspond à celui attendu.
     */
    protected function isMatchingType(array $config, string $expected): bool
    {
        return ($config['type'] ?? null) === $expected;
    }

    /**
     * Ajoute la règle 'required' si le champ n'est pas nullable.
     */
    protected function applyRequiredIfNeeded(array $config, array $rules): array
    {
        if (! ($config['nullable'] ?? false)) {
            array_unshift($rules, 'required');
        }

        return $rules;
    }
}
