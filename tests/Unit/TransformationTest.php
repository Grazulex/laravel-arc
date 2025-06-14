<?php

use Grazulex\Arc\Examples\TransformationExampleDTO;
use Grazulex\Arc\Transformers\TrimTransformer;
use Grazulex\Arc\Transformers\LowercaseTransformer;
use Grazulex\Arc\Transformers\UppercaseTransformer;
use Grazulex\Arc\Transformers\HashTransformer;
use Grazulex\Arc\Transformation\TransformationManager;

describe('Transformation Pipeline', function () {
    
    describe('Individual Transformers', function () {
        
        it('TrimTransformer removes whitespace', function () {
            $transformer = new TrimTransformer();
            
            expect($transformer->transform('  hello world  '))->toBe('hello world');
            expect($transformer->transform("\t\ntest\r\n"))->toBe('test');
            expect($transformer->transform(123))->toBe(123); // Non-strings unchanged
        });
        
        it('LowercaseTransformer converts to lowercase', function () {
            $transformer = new LowercaseTransformer();
            
            expect($transformer->transform('HELLO WORLD'))->toBe('hello world');
            expect($transformer->transform('MixedCASE'))->toBe('mixedcase');
            expect($transformer->transform(123))->toBe(123); // Non-strings unchanged
        });
        
        it('UppercaseTransformer converts to uppercase', function () {
            $transformer = new UppercaseTransformer();
            
            expect($transformer->transform('hello world'))->toBe('HELLO WORLD');
            expect($transformer->transform('MixedCASE'))->toBe('MIXEDCASE');
            expect($transformer->transform(123))->toBe(123); // Non-strings unchanged
        });
        
        it('HashTransformer creates hash', function () {
            $transformer = new HashTransformer();
            
            $result = $transformer->transform('password123');
            expect($result)->toBeString();
            expect(strlen($result))->toBe(64); // SHA256 produces 64 character hex string
            expect($result)->not->toBe('password123'); // Should be different from input
        });
        
    });
    
    describe('Transformation Manager', function () {
        
        it('applies multiple transformers in sequence', function () {
            $transformers = [TrimTransformer::class, LowercaseTransformer::class];
            
            $result = TransformationManager::transform('  HELLO WORLD  ', $transformers);
            expect($result)->toBe('hello world');
        });
        
        it('handles empty transformer array', function () {
            $result = TransformationManager::transform('test', []);
            expect($result)->toBe('test');
        });
        
        it('shouldTransform returns false for null values', function () {
            expect(TransformationManager::shouldTransform(null, [TrimTransformer::class]))->toBeFalse();
        });
        
        it('shouldTransform returns false for empty transformers', function () {
            expect(TransformationManager::shouldTransform('test', []))->toBeFalse();
        });
        
        it('shouldTransform returns true for valid input', function () {
            expect(TransformationManager::shouldTransform('test', [TrimTransformer::class]))->toBeTrue();
        });
        
    });
    
    describe('DTO Integration', function () {
        
        it('applies transformations when setting properties', function () {
            // Skipping this test as it requires Laravel validation framework
            expect(true)->toBeTrue();
        })->skip('Requires Laravel validation framework');
        
        it('handles null values gracefully', function () {
            // Skipping this test as it requires Laravel validation framework
            expect(true)->toBeTrue();
        })->skip('Requires Laravel validation framework');
        
        it('transformations work with direct property assignment', function () {
            // Skipping this test as it requires Laravel validation framework
            expect(true)->toBeTrue();
        })->skip('Requires Laravel validation framework');
        
    });
    
});

