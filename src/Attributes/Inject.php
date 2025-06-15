<?php

declare(strict_types=1);

namespace Grazulex\Arc\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class Inject
{
    public function __construct(
        public readonly string $key,
    ) {}
}
