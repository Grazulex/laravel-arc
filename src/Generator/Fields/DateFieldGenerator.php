<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Fields;

use Grazulex\LaravelArc\Contracts\FieldGenerator;
use Grazulex\LaravelArc\Support\FieldBuilder;

final class DateFieldGenerator implements FieldGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'date';
    }

    public function generate(string $name, array $config): string
    {
        return FieldBuilder::generate($name, 'date', $config);
    }
}
