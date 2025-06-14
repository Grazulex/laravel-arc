<?php

namespace Grazulex\Arc\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EnumProperty extends Property
{
    public function __construct(
        public readonly string $enumClass,
        bool $required = true,
        mixed $default = null,
        ?string $validation = null,
    ) {
        parent::__construct(
            type: 'enum',
            required: $required,
            default: $default,
            validation: $validation,
            cast: 'enum',
            nested: $enumClass,
        );
    }
}
