<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Contracts;

use Grazulex\LaravelArc\Generator\DtoGenerationContext;

interface OptionGenerator
{
    public function generate(string $name, mixed $value, DtoGenerationContext $context): string;
}
