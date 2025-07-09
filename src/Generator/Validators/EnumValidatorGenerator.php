<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Validators;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;

final class EnumValidatorGenerator implements ValidatorGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'enum';
    }

    public function generate(string $name, array $config): ?string
    {
        // Enum PHP native ?
        if (isset($config['enum'])) {
            return "Rule::enum({$config['enum']}::class)";
        }

        // Enum simple (valeurs fixées)
        if (! isset($config['values']) || ! is_array($config['values'])) {
            return null;
        }

        $inList = implode(',', array_map('trim', $config['values']));

        return "in:{$inList}";
    }
}
