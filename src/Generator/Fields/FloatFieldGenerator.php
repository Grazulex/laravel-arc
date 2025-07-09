<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Fields;

use Grazulex\LaravelArc\Contracts\FieldGenerator;
use Grazulex\LaravelArc\Support\FieldBuilder;

final class FloatFieldGenerator implements FieldGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'float' || $type === 'double';
    }

    public function generate(string $name, array $definition): string
    {
        return FieldBuilder::generate($name, 'float', $definition);
    }
}
