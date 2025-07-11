<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;
use Throwable;

/**
 * Validation rule to ensure that the specified enum class exists and is a valid enum.
 */
final class EnumExists implements Rule
{
    public function __construct(private string $enumClass)
    {
        //
    }

    public function passes($attribute, $value): bool
    {
        // Vérifier que la classe existe
        if (! class_exists($this->enumClass)) {
            return false;
        }

        // Vérifier que c'est bien un enum
        if (! enum_exists($this->enumClass)) {
            return false;
        }

        // Si on arrive ici, l'enum existe et est valide
        // On peut maintenant vérifier que la valeur est valide
        try {
            // Essayer de créer l'enum depuis la valeur
            if (method_exists($this->enumClass, 'tryFrom')) {
                return $this->enumClass::tryFrom($value) !== null;
            }

            // Pour les enums purs, vérifier les noms
            $cases = $this->enumClass::cases();
            foreach ($cases as $case) {
                if ($case->name === $value) {
                    return true;
                }
            }

            return false;
        } catch (Throwable) {
            return false;
        }
    }

    public function message(): string
    {
        return "The :attribute field must be a valid case of the {$this->enumClass} enum.";
    }
}
