<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Contracts;

interface OptionGenerator
{
    public function generate(mixed $value): ?string;
}
