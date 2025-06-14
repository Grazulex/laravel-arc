<?php

namespace Tests\Feature;

use Grazulex\Arc\LaravelArcDTO;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Transformers\SlugTransformer;
use Grazulex\Arc\Transformers\TrimTransformer;
use Tests\TestCase;

/**
 * Simple transformer for title to slug conversion in tests.
 */
class TitleToSlugTransformer extends SlugTransformer
{
    public function __construct()
    {
        parent::__construct(sourceField: 'title');
    }
}

/**
 * Name to slug transformer with max length.
 */
class NameToSlugTransformer extends SlugTransformer
{
    public function __construct()
    {
        parent::__construct(sourceField: 'name', maxLength: 20);
    }
}

/**
 * Simple slug transformer with underscore separator.
 */
class UnderscoreSlugTransformer extends SlugTransformer
{
    public function __construct()
    {
        parent::__construct(separator: '_');
    }
}

/**
 * Test d'intégration pour vérifier que le SlugTransformer fonctionne 
 * correctement avec les DTOs complets.
 */
class SlugTransformerIntegrationTest extends TestCase
{
    public function test_slug_generated_from_title_field(): void
    {
        $dto = new class extends LaravelArcDTO {
            #[Property(type: 'string', required: true)]
            public string $title;
            
            #[Property(
                type: 'string',
                required: false,
                transform: [TitleToSlugTransformer::class]
            )]
            public ?string $slug;
        };
        
        $instance = new $dto([
            'title' => 'Hello World Article',
            'slug' => '' // This should be overwritten by the transformer
        ]);
        
        $this->assertEquals('Hello World Article', $instance->title);
        $this->assertEquals('hello-world-article', $instance->slug);
    }
    
    public function test_slug_with_trim_and_length_limit(): void
    {
        $dto = new class extends LaravelArcDTO {
            #[Property(type: 'string', required: false, transform: [TrimTransformer::class])]
            public ?string $name;
            
            #[Property(
                type: 'string',
                required: false,
                transform: [NameToSlugTransformer::class]
            )]
            public ?string $slug;
        };
        
        $instance = new $dto([
            'name' => '  This is a Very Long Product Name  ',
            'slug' => null
        ]);
        
        $this->assertEquals('This is a Very Long Product Name', $instance->name);
        $this->assertEquals('this-is-a-very-long', $instance->slug);
        $this->assertTrue(strlen($instance->slug) <= 20); // Verify it respects maxLength
    }
    
    public function test_slug_transforms_own_value_when_provided(): void
    {
        $dto = new class extends LaravelArcDTO {
            #[Property(type: 'string', required: false)]
            public ?string $title;
            
            #[Property(
                type: 'string',
                required: false,
                transform: [UnderscoreSlugTransformer::class]
            )]
            public ?string $slug;
        };
        
        $instance = new $dto([
            'title' => 'Some Title',
            'slug' => 'Custom Slug Value!'
        ]);
        
        $this->assertEquals('Some Title', $instance->title);
        $this->assertEquals('custom_slug_value', $instance->slug);
    }
}

