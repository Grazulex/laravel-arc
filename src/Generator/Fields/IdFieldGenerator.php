<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Fields;

use Grazulex\LaravelArc\Contracts\FieldGenerator;
use Grazulex\LaravelArc\Support\FieldBuilder;

final class IdFieldGenerator implements FieldGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'id';
    }

    public function generate(string $name, array $config): string
    {
        return FieldBuilder::generate($name, 'id', $config);
    }
}
