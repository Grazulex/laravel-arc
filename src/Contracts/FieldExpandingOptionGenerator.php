<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Contracts;

interface FieldExpandingOptionGenerator extends OptionGenerator
{
    /** @return array<string, array> */
    public function expandFields(mixed $value): array;
}
