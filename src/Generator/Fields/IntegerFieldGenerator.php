<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Fields;

use Grazulex\LaravelArc\Contracts\FieldGenerator;
use Grazulex\LaravelArc\Support\FieldBuilder;

final class IntegerFieldGenerator implements FieldGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'integer' || $type === 'int';
    }

    public function generate(string $name, array $definition): string
    {
        return FieldBuilder::generate($name, 'integer', $definition);
    }
}
