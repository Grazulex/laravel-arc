<?php

namespace Tests\Unit\Transformers;

use Grazulex\Arc\Transformers\SlugTransformer;
use PHPUnit\Framework\TestCase;

class SlugTransformerContextTest extends TestCase
{
    public function test_slug_transforms_from_context_field(): void
    {
        // Créer un transformer qui utilise le champ 'title' du contexte
        $transformer = new SlugTransformer(
            sourceField: 'title',
            separator: '-',
            language: null,
            maxLength: null
        );
        
        $context = [
            'title' => 'My Blog Post Title',
            'slug' => '', // Cette valeur sera transformée
        ];
        
        $result = $transformer->transform('', $context);
        
        $this->assertEquals('my-blog-post-title', $result);
    }
    
    public function test_slug_with_max_length_from_context(): void
    {
        $transformer = new SlugTransformer(
            sourceField: 'name',
            separator: '-',
            language: null,
            maxLength: 15
        );
        
        $context = [
            'name' => 'Very Long Product Name That Exceeds Limit',
            'slug' => ''
        ];
        
        $result = $transformer->transform('', $context);
        
        $this->assertEquals('very-long', $result);
        $this->assertTrue(strlen($result) <= 15);
    }
    
    public function test_slug_preserves_original_when_no_source_field(): void
    {
        $transformer = new SlugTransformer(
            sourceField: 'missing_field',
            separator: '-'
        );
        
        $context = [
            'title' => 'Some Title',
            // missing_field n'existe pas
        ];
        
        $result = $transformer->transform('original-slug', $context);
        
        $this->assertEquals('original-slug', $result);
    }
    
    public function test_should_transform_logic_with_context(): void
    {
        $transformer = new SlugTransformer(
            sourceField: 'title'
        );
        
        // Doit transformer si le champ source existe et n'est pas vide
        $contextWithTitle = ['title' => 'Some Title'];
        $this->assertTrue($transformer->shouldTransform('', $contextWithTitle));
        
        // Ne doit pas transformer si le champ source est vide
        $contextEmptyTitle = ['title' => ''];
        $this->assertFalse($transformer->shouldTransform('', $contextEmptyTitle));
        
        // Ne doit pas transformer si le champ source n'existe pas
        $contextMissingTitle = [];
        $this->assertFalse($transformer->shouldTransform('', $contextMissingTitle));
    }
    
    public function test_multiple_transformers_in_context(): void
    {
        // Simuler un scénario où on a plusieurs champs qui pourraient être utilisés
        $transformer1 = new SlugTransformer(sourceField: 'title_fr');
        $transformer2 = new SlugTransformer(sourceField: 'title_en');
        
        $context = [
            'title_fr' => 'Article en Français',
            'title_en' => 'Article in English',
            'slug_fr' => '',
            'slug_en' => ''
        ];
        
        $slugFr = $transformer1->transform('', $context);
        $slugEn = $transformer2->transform('', $context);
        
        $this->assertEquals('article-en-français', $slugFr);
        $this->assertEquals('article-in-english', $slugEn);
    }
}

