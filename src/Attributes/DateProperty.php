<?php

namespace Grazulex\Arc\Attributes;

use Attribute;

/**
 * Attribute for date properties with automatic Carbon transformation.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class DateProperty extends Property
{
    public function __construct(
        bool $required = true,
        mixed $default = null,
        ?string $validation = null,
        public readonly string $format = 'Y-m-d H:i:s',
        public readonly ?string $timezone = null,
        public readonly bool $immutable = false,
    ) {
        parent::__construct(
            type: $immutable ? 'CarbonImmutable' : 'Carbon',
            required: $required,
            default: $default,
            validation: $validation,
            cast: 'date',
            dateFormat: $format,
        );
    }
}
