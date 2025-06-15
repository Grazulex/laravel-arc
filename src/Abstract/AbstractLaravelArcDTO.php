<?php

declare(strict_types=1);

namespace Grazulex\Arc\Abstract;

use Grazulex\Arc\Traits\DTOFactoryTrait;

/**
 * Base class for Laravel Arc DTOs that can be extended.
 *
 * This class provides the same functionality as LaravelArcDTO but can be extended,
 * which is particularly useful for testing purposes.
 */
abstract class AbstractLaravelArcDTO extends AbstractDTO
{
    use DTOFactoryTrait;
}
