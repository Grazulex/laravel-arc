<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator\Options;

use Grazulex\LaravelArc\Contracts\OptionGenerator;

final class TimestampsOptionGenerator implements OptionGenerator
{
    public function generate(mixed $value): ?string
    {
        return $value ? 'public bool $timestamps = true;' : null;
    }
}
