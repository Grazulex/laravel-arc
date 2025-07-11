<?php

declare(strict_types=1);

namespace App\Enums;

enum Visibility: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case PROTECTED = 'protected';
    case INTERNAL = 'internal';
}
