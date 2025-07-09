<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Fields;

use Grazulex\LaravelArc\Contracts\FieldGenerator;

final class IntegerFieldGenerator implements FieldGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'integer' || $type === 'int';
    }

    public function generate(string $name, array $definition): string
    {
        $default = isset($definition['default']) ? (int) $definition['default'] : '0';

        return "public int \${$name} = {$default};";
    }
}
