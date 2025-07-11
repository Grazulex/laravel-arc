<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Validation\Rules;

use BackedEnum;
use Illuminate\Contracts\Validation\Rule;
use Throwable;

/**
 * Validation rule to ensure that the value is a valid case of the specified enum class.
 * This is an alternative to Laravel's built-in 'enum:' rule with additional checks.
 */
final class InEnum implements Rule
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

        // Vérifier que la valeur correspond à un cas de l'enum
        try {
            $cases = $this->enumClass::cases();

            foreach ($cases as $case) {
                // Pour les enums backed, comparer la valeur
                if ($case instanceof BackedEnum && $case->value === $value) {
                    return true;
                }

                // Pour les enums purs (UnitEnum mais pas BackedEnum), comparer le nom
                if (! ($case instanceof BackedEnum) && $case->name === $value) {
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
        return "The :attribute field must be a valid {$this->enumClass} value.";
    }
}
