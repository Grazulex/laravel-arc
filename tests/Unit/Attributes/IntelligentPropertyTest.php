<?php

namespace Tests\Unit\Attributes;

use Carbon\Carbon;
use Grazulex\Arc\Attributes\Property;
use PHPUnit\Framework\TestCase;
use Tests\Examples\TestEnum;
use Tests\Examples\TestStatus;
use Tests\Examples\UserDTO;

class IntelligentPropertyTest extends TestCase
{
    public function test_property_detects_basic_types_automatically(): void
    {
        // String
        $property = new Property('string');
        $this->assertSame('string', $property->cast);
        $this->assertNull($property->nested);

        // Integer
        $property = new Property('int');
        $this->assertSame('int', $property->cast);

        $property = new Property('integer');
        $this->assertSame('int', $property->cast);

        // Float
        $property = new Property('float');
        $this->assertSame('float', $property->cast);

        $property = new Property('double');
        $this->assertSame('float', $property->cast);

        // Boolean
        $property = new Property('bool');
        $this->assertSame('bool', $property->cast);

        $property = new Property('boolean');
        $this->assertSame('bool', $property->cast);

        // Array
        $property = new Property('array');
        $this->assertSame('array', $property->cast);
    }

    public function test_property_detects_carbon_dates_automatically(): void
    {
        $property = new Property('Carbon');
        $this->assertSame('date', $property->cast);

        $property = new Property('CarbonImmutable');
        $this->assertSame('date', $property->cast);
    }

    public function test_property_handles_explicit_date_configuration(): void
    {
        $property = new Property('Carbon', format: 'Y-m-d', timezone: 'UTC', immutable: true);
        $this->assertSame('date', $property->cast);
        // Note: dateFormat, timezone, and immutable are not stored in base Property anymore
        // They're handled by specialized DateProperty subclass
    }

    public function test_property_handles_enum_with_explicit_class(): void
    {
        $property = new Property('UserStatus', enumClass: 'App\\Enums\\UserStatus');
        $this->assertSame('enum', $property->cast);
        $this->assertSame('App\\Enums\\UserStatus', $property->nested);
    }

    public function test_property_handles_dto_with_explicit_class(): void
    {
        $property = new Property('UserDTO', dtoClass: 'App\\DTOs\\UserDTO');
        $this->assertSame('nested', $property->cast);
        $this->assertSame('App\\DTOs\\UserDTO', $property->nested);
    }

    public function test_property_detects_collections(): void
    {
        // Array notation
        $property = new Property('array<UserDTO>');
        $this->assertSame('nested', $property->cast);
        $this->assertSame('UserDTO', $property->nested);
        $this->assertTrue($property->isCollection);

        // Explicit collection flag
        $property = new Property('UserDTO', collection: true, dtoClass: 'App\\DTOs\\UserDTO');
        $this->assertSame('nested', $property->cast);
        $this->assertSame('App\\DTOs\\UserDTO', $property->nested);
        $this->assertTrue($property->isCollection);

        // Array with bracket notation
        $property = new Property('UserDTO[]');
        $this->assertTrue($property->isCollection);
    }

    public function test_property_allows_manual_cast_override(): void
    {
        // Manual override should take precedence
        $property = new Property('string', cast: 'custom');
        $this->assertSame('custom', $property->cast);
    }

    public function test_property_handles_mixed_scenarios(): void
    {
        // Complex array type
        $property = new Property('array<Carbon>');
        $this->assertSame('nested', $property->cast);
        $this->assertSame('Carbon', $property->nested);
        $this->assertTrue($property->isCollection);

        // Default fallback
        $property = new Property('SomeUnknownType');
        $this->assertSame('string', $property->cast); // fallback to string
    }

    public function test_property_maintains_backward_compatibility(): void
    {
        // Old explicit way should still work
        $property = new Property('string', cast: 'string');
        $this->assertSame('string', $property->cast);

        $property = new Property('Carbon', cast: 'date', format: 'Y-m-d');
        $this->assertSame('date', $property->cast);
    }
}

