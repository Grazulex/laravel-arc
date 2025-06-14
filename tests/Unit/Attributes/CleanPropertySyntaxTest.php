<?php

namespace Tests\Unit\Attributes;

use Carbon\Carbon;
use Grazulex\Arc\Attributes\Property;
use PHPUnit\Framework\TestCase;
use Tests\Examples\UserDTO;

// Test enum for this test
enum TestStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
}

class CleanPropertySyntaxTest extends TestCase
{
    public function test_clean_enum_syntax_works(): void
    {
        // New clean syntax: type='enum', class=...
        $property = new Property(type: 'enum', class: TestStatus::class);
        
        $this->assertSame('enum', $property->cast);
        $this->assertSame(TestStatus::class, $property->nested);
        $this->assertFalse($property->isCollection);
    }
    
    public function test_clean_date_syntax_works(): void
    {
        // New clean syntax: type='date'
        $property = new Property(type: 'date', format: 'Y-m-d');
        
        $this->assertSame('date', $property->cast);
        $this->assertNull($property->nested);
        $this->assertFalse($property->isCollection);
    }
    
    public function test_clean_nested_syntax_works(): void
    {
        // New clean syntax: type='nested', class=...
        $property = new Property(type: 'nested', class: UserDTO::class);
        
        $this->assertSame('nested', $property->cast);
        $this->assertSame(UserDTO::class, $property->nested);
        $this->assertFalse($property->isCollection);
    }
    
    public function test_clean_collection_syntax_works(): void
    {
        // New clean syntax: type='collection', class=...
        $property = new Property(type: 'collection', class: UserDTO::class);
        
        $this->assertSame('nested', $property->cast); // Collections use nested caster
        $this->assertSame(UserDTO::class, $property->nested);
        $this->assertTrue($property->isCollection);
    }
    
    public function test_basic_types_work_with_clean_syntax(): void
    {
        $stringProperty = new Property(type: 'string');
        $this->assertSame('string', $stringProperty->cast);
        
        $intProperty = new Property(type: 'int');
        $this->assertSame('int', $intProperty->cast);
        
        $floatProperty = new Property(type: 'float');
        $this->assertSame('float', $floatProperty->cast);
        
        $boolProperty = new Property(type: 'bool');
        $this->assertSame('bool', $boolProperty->cast);
    }
    
    public function test_backward_compatibility_maintained(): void
    {
        // Old syntax should still work - specify the class explicitly
        $enumProperty = new Property(type: 'enum', enumClass: TestStatus::class);
        $this->assertSame('enum', $enumProperty->cast);
        $this->assertSame(TestStatus::class, $enumProperty->nested);
        
        // Array<DTO> syntax should still work
        $collectionProperty = new Property('array<UserDTO>');
        $this->assertSame('nested', $collectionProperty->cast);
        $this->assertSame('UserDTO', $collectionProperty->nested);
        $this->assertTrue($collectionProperty->isCollection);
        
        // Test that legacy enumClass still works with explicit enum type
        $legacyEnum = new Property(type: 'SomeRandomType', enumClass: TestStatus::class);
        $this->assertSame('enum', $legacyEnum->cast);
        $this->assertSame(TestStatus::class, $legacyEnum->nested);
    }
    
    public function test_new_class_parameter_takes_priority(): void
    {
        // New 'class' parameter should override legacy parameters
        $property = new Property(
            type: 'enum',
            class: TestStatus::class,
            enumClass: 'SomeOtherClass', // Should be ignored
            dtoClass: 'AnotherClass' // Should be ignored
        );
        
        $this->assertSame(TestStatus::class, $property->nested);
    }
}

