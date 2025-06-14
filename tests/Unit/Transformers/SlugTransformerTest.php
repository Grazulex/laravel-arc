<?php

namespace Tests\Unit\Transformers;

use Grazulex\Arc\Transformers\SlugTransformer;
use PHPUnit\Framework\TestCase;

class SlugTransformerTest extends TestCase
{
    public function test_transforms_string_to_slug(): void
    {
        $transformer = SlugTransformer::make();
        
        $result = $transformer->transform('Hello World!');
        
        $this->assertEquals('hello-world', $result);
    }
    
    public function test_transforms_from_context_field(): void
    {
        $transformer = SlugTransformer::from('title');
        
        $context = ['title' => 'My Blog Post Title'];
        $result = $transformer->transform('', $context);
        
        $this->assertEquals('my-blog-post-title', $result);
    }
    
    public function test_respects_max_length(): void
    {
        $transformer = SlugTransformer::make(['maxLength' => 10]);
        
        $result = $transformer->transform('This is a very long title that should be truncated');
        
        $this->assertEquals('this-is-a', $result);
        $this->assertTrue(strlen($result) <= 10);
    }
    
    public function test_custom_separator(): void
    {
        $transformer = SlugTransformer::make(['separator' => '_']);
        
        $result = $transformer->transform('Hello World');
        
        $this->assertEquals('hello_world', $result);
    }
    
    public function test_language_support(): void
    {
        $transformer = SlugTransformer::make(['language' => 'de']);
        
        $result = $transformer->transform('Müller Straße');
        
        $this->assertEquals('mueller-strasse', $result);
    }
    
    public function test_should_transform_with_source_field(): void
    {
        $transformer = SlugTransformer::from('title');
        
        $context = ['title' => 'My Title'];
        $this->assertTrue($transformer->shouldTransform('', $context));
        
        $contextEmpty = ['title' => ''];
        $this->assertFalse($transformer->shouldTransform('', $contextEmpty));
        
        $contextMissing = [];
        $this->assertFalse($transformer->shouldTransform('', $contextMissing));
    }
    
    public function test_should_transform_without_source_field(): void
    {
        $transformer = SlugTransformer::make();
        
        $this->assertTrue($transformer->shouldTransform('Hello'));
        $this->assertFalse($transformer->shouldTransform(''));
        $this->assertFalse($transformer->shouldTransform(null));
    }
    
    public function test_preserves_original_value_when_no_source(): void
    {
        $transformer = SlugTransformer::from('missing_field');
        
        $result = $transformer->transform('original-value', []);
        
        $this->assertEquals('original-value', $result);
    }
    
    public function test_handles_special_characters(): void
    {
        $transformer = SlugTransformer::make();
        
        $result = $transformer->transform('Café & Restaurant (2024)!');
        
        // Laravel's Str::slug keeps accents by default
        $this->assertEquals('café-restaurant-2024', $result);
    }
    
    public function test_max_length_preserves_word_boundaries(): void
    {
        $transformer = SlugTransformer::make(['maxLength' => 15]);
        
        $result = $transformer->transform('hello-world-this-is-long');
        
        // Should cut at word boundary, not in the middle
        $this->assertEquals('hello-world', $result);
        $this->assertStringEndsNotWith('-', $result);
    }
}

