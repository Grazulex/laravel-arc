<?php

namespace Grazulex\LaravelArc\Generator\Fields;

use Grazulex\LaravelArc\Contracts\FieldGenerator;
use Grazulex\LaravelArc\Support\FieldBuilder;

class DateTimeFieldGenerator implements FieldGenerator
{
    public function supports(string $type): bool
    {
        return $type === 'datetime';
    }

    public function generate(string $name, array $config): string
    {
        return FieldBuilder::generate($name, 'datetime', $config);
    }
}
