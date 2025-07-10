<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Contracts;

interface FieldExpandingOptionGenerator extends OptionGenerator
{
    public function expandFields(mixed $value): array;
}
