<?php

use Carbon\Carbon;
use Grazulex\Arc\Attributes\DateProperty;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Casting\CastManager;
use Grazulex\Arc\Casting\Casters\ArrayCaster;
use Grazulex\Arc\Casting\Casters\BooleanCaster;
use Grazulex\Arc\Casting\Casters\DateCaster;
use Grazulex\Arc\Casting\Casters\EnumCaster;
use Grazulex\Arc\Casting\Casters\FloatCaster;
use Grazulex\Arc\Casting\Casters\IntegerCaster;
use Grazulex\Arc\Casting\Casters\NestedCaster;
use Grazulex\Arc\Casting\Casters\StringCaster;
use Grazulex\Arc\Casting\Contracts\CasterInterface;
use Grazulex\Arc\Examples\UserDTO;

describe('Refactored Casting Architecture', function () {
    it('can use dedicated casters for different types', function () {
        $manager = new CastManager();
        
        // Check that we have the expected casters (5 basic + 3 advanced)
        $casters = $manager->getCasters();
        expect($casters)->toHaveCount(8);
        
        // Basic type casters
        expect($casters[0])->toBeInstanceOf(StringCaster::class);
        expect($casters[1])->toBeInstanceOf(IntegerCaster::class);
        expect($casters[2])->toBeInstanceOf(FloatCaster::class);
        expect($casters[3])->toBeInstanceOf(BooleanCaster::class);
        expect($casters[4])->toBeInstanceOf(ArrayCaster::class);
        
        // Advanced type casters
        expect($casters[5])->toBeInstanceOf(DateCaster::class);
        expect($casters[6])->toBeInstanceOf(NestedCaster::class);
        expect($casters[7])->toBeInstanceOf(EnumCaster::class);
    });
    
    it('can register new casters', function () {
        $manager = new CastManager();
        
        // Create a custom caster
        $customCaster = new class implements CasterInterface {
            public function canCast(string $castType): bool
            {
                return $castType === 'custom';
            }
            
            public function cast(mixed $value, Property $attribute): mixed
            {
                return 'cast_' . $value;
            }
            
            public function serialize(mixed $value, Property $attribute): mixed
            {
                return str_replace('cast_', '', $value);
            }
        };
        
        $manager->registerCaster($customCaster);
        
        expect($manager->getCasters())->toHaveCount(9); // 8 default + 1 custom
    });
    
    it('maintains compatibility with static cast method', function () {
        $dateProperty = new DateProperty();
        
        $result = CastManager::cast('2023-01-01', $dateProperty);
        
        expect($result)->toBeInstanceOf(Carbon::class);
        expect($result->format('Y-m-d'))->toBe('2023-01-01');
    });
    
    it('maintains compatibility with static serialize method', function () {
        $dateProperty = new DateProperty();
        
        $date = Carbon::parse('2023-01-01');
        $result = CastManager::serialize($date, $dateProperty);
        
        expect($result)->toBeString();
        expect($result)->toContain('2023-01-01');
    });
    
    it('handles unknown cast types gracefully', function () {
        $property = new Property(type: 'unknown_type', cast: 'unknown');
        
        $result = CastManager::cast('test_value', $property);
        
        // Should return the value as-is for unknown cast types
        expect($result)->toBe('test_value');
    });
    
    it('handles null values consistently across all casters', function () {
        $dateProperty = new DateProperty();
        
        $result = CastManager::cast(null, $dateProperty);
        
        expect($result)->toBeNull();
    });
});

describe('Individual Caster Functionality', function () {
    it('DateCaster can handle date casting correctly', function () {
        $caster = new DateCaster();
        $property = new DateProperty();
        
        expect($caster->canCast('date'))->toBeTrue();
        expect($caster->canCast('string'))->toBeFalse();
        
        $result = $caster->cast('2023-01-01', $property);
        expect($result)->toBeInstanceOf(Carbon::class);
        
        $serialized = $caster->serialize($result, $property);
        expect($serialized)->toBeString();
    });
    
    it('NestedCaster can identify nested cast types', function () {
        $caster = new NestedCaster();
        
        expect($caster->canCast('nested'))->toBeTrue();
        expect($caster->canCast('date'))->toBeFalse();
    });
    
    it('EnumCaster can identify enum cast types', function () {
        $caster = new EnumCaster();
        
        expect($caster->canCast('enum'))->toBeTrue();
        expect($caster->canCast('string'))->toBeFalse();
    });
    
    it('handles all basic types through CastManager', function () {
        // Test string casting
        $stringProperty = new Property('string', cast: 'string');
        expect(CastManager::cast(123, $stringProperty))->toBe('123');
        
        // Test integer casting
        $intProperty = new Property('int', cast: 'int');
        expect(CastManager::cast('123', $intProperty))->toBe(123);
        
        // Test float casting
        $floatProperty = new Property('float', cast: 'float');
        expect(CastManager::cast('123.45', $floatProperty))->toBe(123.45);
        
        // Test boolean casting
        $boolProperty = new Property('bool', cast: 'bool');
        expect(CastManager::cast('true', $boolProperty))->toBe(true);
        expect(CastManager::cast('false', $boolProperty))->toBe(false);
        
        // Test array casting
        $arrayProperty = new Property('array', cast: 'array');
        $jsonString = '{"name":"John"}';
        $result = CastManager::cast($jsonString, $arrayProperty);
        expect($result)->toBeArray();
        expect($result['name'])->toBe('John');
    });
});

