<?php

namespace Tests\Unit\Casting;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Casting\Casters\ArrayCaster;
use Grazulex\Arc\Casting\Casters\BooleanCaster;
use Grazulex\Arc\Casting\Casters\FloatCaster;
use Grazulex\Arc\Casting\Casters\IntegerCaster;
use Grazulex\Arc\Casting\Casters\StringCaster;
use PHPUnit\Framework\TestCase;

class BasicCastersTest extends TestCase
{
    public function test_string_caster_functionality(): void
    {
        $caster = new StringCaster();
        $attribute = new Property('string', cast: 'string');

        // Test basic functionality
        $this->assertTrue($caster->canCast('string'));
        $this->assertFalse($caster->canCast('int'));

        // Test casting
        $this->assertSame('hello', $caster->cast('hello', $attribute));
        $this->assertSame('123', $caster->cast(123, $attribute));
        $this->assertSame('1', $caster->cast(true, $attribute));

        // Test serialization
        $this->assertSame('hello', $caster->serialize('hello', $attribute));
        $this->assertSame('123', $caster->serialize(123, $attribute));
    }

    public function test_integer_caster_functionality(): void
    {
        $caster = new IntegerCaster();
        $attribute = new Property('int', cast: 'int');

        // Test basic functionality
        $this->assertTrue($caster->canCast('int'));
        $this->assertTrue($caster->canCast('integer'));
        $this->assertFalse($caster->canCast('string'));

        // Test casting
        $this->assertSame(123, $caster->cast(123, $attribute));
        $this->assertSame(123, $caster->cast('123', $attribute));
        $this->assertSame(123, $caster->cast(123.9, $attribute));
        $this->assertSame(1, $caster->cast(true, $attribute));

        // Test serialization
        $this->assertSame(123, $caster->serialize(123, $attribute));
        $this->assertSame(123, $caster->serialize('123', $attribute));
    }

    public function test_float_caster_functionality(): void
    {
        $caster = new FloatCaster();
        $attribute = new Property('float', cast: 'float');

        // Test basic functionality
        $this->assertTrue($caster->canCast('float'));
        $this->assertTrue($caster->canCast('double'));
        $this->assertFalse($caster->canCast('int'));

        // Test casting
        $this->assertSame(123.45, $caster->cast(123.45, $attribute));
        $this->assertSame(123.0, $caster->cast('123', $attribute));
        $this->assertSame(123.0, $caster->cast(123, $attribute));

        // Test serialization
        $this->assertSame(123.45, $caster->serialize(123.45, $attribute));
        $this->assertSame(123.0, $caster->serialize('123', $attribute));
    }

    public function test_boolean_caster_functionality(): void
    {
        $caster = new BooleanCaster();
        $attribute = new Property('bool', cast: 'bool');

        // Test basic functionality
        $this->assertTrue($caster->canCast('bool'));
        $this->assertTrue($caster->canCast('boolean'));
        $this->assertFalse($caster->canCast('string'));

        // Test casting - true values
        $this->assertTrue($caster->cast(true, $attribute));
        $this->assertTrue($caster->cast('true', $attribute));
        $this->assertTrue($caster->cast('1', $attribute));
        $this->assertTrue($caster->cast('yes', $attribute));
        $this->assertTrue($caster->cast('on', $attribute));
        $this->assertTrue($caster->cast(1, $attribute));

        // Test casting - false values
        $this->assertFalse($caster->cast(false, $attribute));
        $this->assertFalse($caster->cast('false', $attribute));
        $this->assertFalse($caster->cast('0', $attribute));
        $this->assertFalse($caster->cast('no', $attribute));
        $this->assertFalse($caster->cast('off', $attribute));
        $this->assertFalse($caster->cast('', $attribute));
        $this->assertFalse($caster->cast(0, $attribute));

        // Test serialization
        $this->assertTrue($caster->serialize(true, $attribute));
        $this->assertFalse($caster->serialize(false, $attribute));
    }

    public function test_array_caster_functionality(): void
    {
        $caster = new ArrayCaster();
        $attribute = new Property('array', cast: 'array');

        // Test basic functionality
        $this->assertTrue($caster->canCast('array'));
        $this->assertFalse($caster->canCast('string'));

        // Test casting
        $array = ['a', 'b', 'c'];
        $this->assertSame($array, $caster->cast($array, $attribute));

        // Test JSON string casting
        $jsonString = '{"name":"John","age":30}';
        $expected = ['name' => 'John', 'age' => 30];
        $this->assertSame($expected, $caster->cast($jsonString, $attribute));

        // Test object casting
        $object = (object) ['name' => 'John', 'age' => 30];
        $expected = ['name' => 'John', 'age' => 30];
        $this->assertSame($expected, $caster->cast($object, $attribute));

        // Test scalar wrapping
        $this->assertSame(['hello'], $caster->cast('hello', $attribute));
        $this->assertSame([123], $caster->cast(123, $attribute));

        // Test serialization
        $this->assertSame($array, $caster->serialize($array, $attribute));
    }

    public function test_null_handling_across_all_basic_casters(): void
    {
        $casters = [
            new StringCaster(),
            new IntegerCaster(),
            new FloatCaster(),
            new BooleanCaster(),
            new ArrayCaster(),
        ];

        $attribute = new Property('string', cast: 'string');

        foreach ($casters as $caster) {
            $this->assertNull($caster->cast(null, $attribute));
            $this->assertNull($caster->serialize(null, $attribute));
        }
    }
}

