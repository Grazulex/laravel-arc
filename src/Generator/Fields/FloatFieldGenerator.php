<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Fields;

use Grazulex\LaravelArc\Contracts\FieldGenerator;

final class FloatFieldGenerator implements FieldGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'float' || $type === 'double';
    }

    public function generate(string $name, array $definition): string
    {
        $default = isset($definition['default']) ? (float) $definition['default'] : '0.0';

        return "public float \${$name} = {$default};";
    }
}
