<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Fields;

use Grazulex\LaravelArc\Contracts\FieldGenerator;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Support\FieldBuilder;

final class StringFieldGenerator implements FieldGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'string';
    }

    public function generate(string $name, array $definition, DtoGenerationContext $context): string
    {
        return FieldBuilder::generate($name, 'string', $definition);
    }
}
