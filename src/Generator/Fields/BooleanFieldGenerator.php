<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Fields;

use Grazulex\LaravelArc\Contracts\FieldGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Support\FieldBuilder;

final class BooleanFieldGenerator implements FieldGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'boolean' || $type === 'bool';
    }

    public function generate(string $name, array $definition, DtoGenerationContext $context): string
    {
        return FieldBuilder::generate($name, 'boolean', $definition);
    }
}
