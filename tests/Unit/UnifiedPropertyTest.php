<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Traits\DTOTrait;
use PHPUnit\Framework\TestCase;

/**
 * Test demonstrating the new unified Property attribute system.
 */
class UnifiedPropertyTest extends TestCase
{
    public function test_unified_property_attribute_works_for_all_types(): void
    {
        $dto = new UnifiedTestDTO([
            'basicString' => 'Hello World',
            'basicInt' => '42', // string should be cast to int
            'basicFloat' => '3.14', // string should be cast to float
            'basicBool' => 'true', // string should be cast to bool
            'basicArray' => '{"key":"value"}', // JSON string should be cast to array
            'smartDate' => '2023-12-25', // string should be cast to Carbon
        ]);

        // Test basic types with auto-detection
        $this->assertSame('Hello World', $dto->basicString);
        $this->assertSame(42, $dto->basicInt);
        $this->assertSame(3.14, $dto->basicFloat);
        $this->assertTrue($dto->basicBool);
        $this->assertSame(['key' => 'value'], $dto->basicArray);
        
        // Test smart date detection
        $this->assertInstanceOf(Carbon::class, $dto->smartDate);
        $this->assertSame('2023-12-25', $dto->smartDate->format('Y-m-d'));
    }

    public function test_explicit_configuration_overrides_auto_detection(): void
    {
        $dto = new UnifiedTestDTO([
            'customCast' => 'some value',
        ]);

        // This should work with custom cast override
        $this->assertSame('some value', $dto->customCast);
    }
}

/**
 * Test DTO using only the unified Property attribute.
 */
class UnifiedTestDTO
{
    use DTOTrait;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }
    // Basic types - automatically detected
    #[Property('string')]
    public string $basicString;

    #[Property('int')]
    public int $basicInt;

    #[Property('float')]
    public float $basicFloat;

    #[Property('bool')]
    public bool $basicBool;

    /** @var array<string, mixed> */
    #[Property('array')]
    public array $basicArray = [];

    // Smart date detection
    #[Property('Carbon')]
    public Carbon $smartDate;

    // Manual cast override
    #[Property('string', cast: 'string')]
    public string $customCast;

    // Collection with auto-detection (commented out as we'd need a real DTO class)
    // #[Property('array<UserDTO>')]
    // public array $users;

    // Explicit enum (commented out as we'd need the actual enum)
    // #[Property('UserStatus', enumClass: UserStatus::class)]
    // public UserStatus $status;
}

