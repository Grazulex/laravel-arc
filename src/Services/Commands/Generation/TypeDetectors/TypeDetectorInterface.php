<?php

declare(strict_types=1);

namespace Grazulex\Arc\Services\Commands\Generation\TypeDetectors;

interface TypeDetectorInterface
{
    public function detect(mixed $value): string;
}
