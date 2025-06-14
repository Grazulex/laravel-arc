<?php

namespace Grazulex\Arc\Attributes;

use Attribute;

/**
 * Attribute for nested DTO properties.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class NestedProperty extends Property
{
    public function __construct(
        public readonly string $dtoClass,
        bool $required = true,
        mixed $default = null,
        ?string $validation = null,
        public readonly bool $isCollection = false,
    ) {
        parent::__construct(
            type: $isCollection ? 'array' : 'object',
            required: $required,
            default: $default,
            validation: $validation,
            cast: 'nested',
            nested: $dtoClass
        );
    }
}

