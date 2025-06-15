<?php

namespace Grazulex\Arc\Traits;

use Grazulex\Arc\Contracts\DTOFactoryInterface;
use Grazulex\Arc\Contracts\DTOInterface;
use Grazulex\Arc\Factory\DTOFactory;

trait DTOFactoryTrait
{
    /**
     * Create a factory instance for this DTO.
     */
    public static function factory(): DTOFactoryInterface
    {
        return new DTOFactory(static::class);
    }

    /**
     * Create a factory with initial attributes.
     *
     * @param array<string, mixed> $attributes
     */
    public static function factoryWithAttributes(array $attributes): DTOFactoryInterface
    {
        return static::factory()->withAttributes($attributes);
    }

    /**
     * Create a factory with fake data.
     */
    public static function factoryFake(): DTOFactoryInterface
    {
        return static::factory()->fake();
    }

    /**
     * Quick method to create a DTO with fake data.
     *
     * @param null|array<string, mixed> $overrides
     */
    public static function fake(?array $overrides = null): DTOInterface|static
    {
        $factory = static::factory()->fake();

        if ($overrides) {
            $factory->withAttributes($overrides);
        }

        /** @var static $result */
        $result = $factory->create();

        return $result;
    }

    /**
     * Quick method to create multiple DTOs with fake data.
     *
     * @param null|array<string, mixed> $overrides
     *
     * @return array<int, DTOInterface|static>
     */
    public static function fakeMany(int $count, ?array $overrides = null): array
    {
        $factory = static::factory();

        if ($overrides) {
            $factory->withAttributes($overrides);
        }

        /** @var array<int, static> $result */
        $result = $factory->fake()->createMany($count);

        return $result;
    }
}
