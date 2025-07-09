<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Fields;

use Grazulex\LaravelArc\Contracts\FieldGenerator;

final class StringFieldGenerator implements FieldGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'string';
    }

    public function generate(string $name, array $definition): string
    {
        $default = $definition['default'] ?? "''";

        return "public string \${$name} = {$default};";
    }
}
