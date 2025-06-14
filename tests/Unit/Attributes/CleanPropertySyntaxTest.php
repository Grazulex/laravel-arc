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
    
    public function test_auto_detection_from_class_parameter(): void
    {
        // Auto-detection when type is not specified but class is provided
        $enumProperty = new Property(type: 'string', class: TestStatus::class);
        $this->assertSame('enum', $enumProperty->cast); // Should auto-detect from class
        $this->assertSame(TestStatus::class, $enumProperty->nested);
        
        // Auto-detection for DTO class
        $dtoProperty = new Property(type: 'string', class: UserDTO::class);
        $this->assertSame('nested', $dtoProperty->cast); // Should auto-detect from class
        $this->assertSame(UserDTO::class, $dtoProperty->nested);
    }
    
    public function test_explicit_type_with_class_parameter(): void
    {
        // Explicit type specification with class parameter
        $property = new Property(
            type: 'enum',
            class: TestStatus::class
        );
        
        $this->assertSame('enum', $property->cast);
        $this->assertSame(TestStatus::class, $property->nested);
    }
}

