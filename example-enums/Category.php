<?php

declare(strict_types=1);

namespace App\Enums;

enum Category: int
{
    case GENERAL = 1;
    case TECH = 2;
    case NEWS = 3;
    case SPORTS = 4;
}
