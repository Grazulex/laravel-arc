<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Options;

use Grazulex\LaravelArc\Contracts\OptionGenerator;

final class SoftDeletesOptionGenerator implements OptionGenerator
{
    public function generate(mixed $value): ?string
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return 'public bool $softDeletes = true;';
        }

        return null;
    }
}
