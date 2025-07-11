<?php

declare(strict_types=1);

namespace Tests\Fixtures\Enums;

enum Status: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
