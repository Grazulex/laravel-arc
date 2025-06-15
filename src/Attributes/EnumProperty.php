<?php

declare(strict_types=1);

namespace Grazulex\Arc\Attributes;

use Attribute;

/**
 * @internal This class is used to mark enum properties in DTOs
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class EnumProperty extends Property
{
    public function __construct(
        string $enumClass,
        bool $required = false,
        mixed $default = null,
        ?string $validation = null,
    ) {
        parent::__construct(
            type: 'enum',
            required: $required,
            default: $default,
            validation: $validation,
            class: $enumClass,
        );
    }
}
