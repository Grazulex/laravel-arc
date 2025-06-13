<?php

namespace Grazulex\Arc\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Property
{
    public function __construct(
        public readonly string $type,
        public readonly bool $required = true,
        public readonly mixed $default = null,
        public readonly ?string $validation = null,
    ) {}
}
