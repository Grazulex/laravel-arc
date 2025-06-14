<?php

namespace Tests\Unit\Examples;

use Grazulex\Arc\Examples\TransformationExampleDTO;
use Tests\TestCase;

class TransformationExampleDTOTest extends TestCase
{
    public function test_basic_transformations(): void
    {
        $dto = new TransformationExampleDTO([
            'title' => '  Test Article Title  ',
            'email' => '  USER@EXAMPLE.COM  ',
            'country_code' => '  fr  ',
            'password_hash' => 'password123',
            'phone' => '  +33 1 23 45 67 89  ',
            'age' => 30,
            'bio' => '  Developer bio  '
        ]);
        
        // Basic transformations
        $this->assertEquals('Test Article Title', $dto->title); // Trimmed
        $this->assertEquals('user@example.com', $dto->email); // Trimmed + lowercase
        $this->assertEquals('FR', $dto->country_code); // Trimmed + uppercase
        $this->assertEquals('+33 1 23 45 67 89', $dto->phone); // Trimmed only
        $this->assertEquals('Developer bio', $dto->bio); // Trimmed
    }
    
    public function test_slug_generation_from_title(): void
    {
        $dto = new TransformationExampleDTO([
            'title' => 'Les Meilleures Pratiques PHP',
            'email' => 'test@example.com',
            'password_hash' => 'password123',
            'age' => 25
        ]);
        
        $this->assertEquals('Les Meilleures Pratiques PHP', $dto->title);
        $this->assertEquals('les-meilleures-pratiques-php', $dto->slug);
    }
    
    public function test_slug_length_limit(): void
    {
        $dto = new TransformationExampleDTO([
            'title' => 'This is a Very Long Article Title That Should Be Truncated at Fifty Characters Maximum',
            'email' => 'test@example.com',
            'password_hash' => 'password123',
            'age' => 25
        ]);
        
        $this->assertTrue(strlen($dto->slug) <= 50);
        $this->assertStringStartsWith('this-is-a-very-long-article-title', $dto->slug);
    }
    
    public function test_full_name_generation(): void
    {
        $dto = new TransformationExampleDTO([
            'title' => 'Test Article',
            'first_name' => '  Jean-Marc  ',
            'last_name' => '  Strauven  ',
            'email' => 'test@example.com',
            'password_hash' => 'password123',
            'age' => 35
        ]);
        
        $this->assertEquals('Jean-Marc', $dto->first_name);
        $this->assertEquals('Strauven', $dto->last_name);
        $this->assertEquals('Jean-Marc Strauven', $dto->full_name);
    }
    
    public function test_username_generation_from_full_name(): void
    {
        $dto = new TransformationExampleDTO([
            'title' => 'Test Article',
            'first_name' => 'Marie',
            'last_name' => 'Dupont',
            'email' => 'test@example.com',
            'password_hash' => 'password123',
            'age' => 28
        ]);
        
        $this->assertEquals('Marie Dupont', $dto->full_name);
        $this->assertEquals('marie_dupont', $dto->username);
    }
    
    public function test_partial_names(): void
    {
        // Test with only first name
        $dto1 = new TransformationExampleDTO([
            'title' => 'Test Article',
            'first_name' => 'Alice',
            'email' => 'test@example.com',
            'password_hash' => 'password123',
            'age' => 25
        ]);
        
        $this->assertEquals('Alice', $dto1->full_name);
        $this->assertEquals('alice', $dto1->username);
        
        // Test with only last name
        $dto2 = new TransformationExampleDTO([
            'title' => 'Test Article',
            'last_name' => 'Smith',
            'email' => 'test@example.com',
            'password_hash' => 'password123',
            'age' => 25
        ]);
        
        $this->assertEquals('Smith', $dto2->full_name);
        $this->assertEquals('smith', $dto2->username);
    }
    
    public function test_username_length_limit(): void
    {
        $dto = new TransformationExampleDTO([
            'title' => 'Test Article',
            'first_name' => 'Jean-François-Marie-Antoine',
            'last_name' => 'de La Rochefoucauld-Liancourt',
            'email' => 'test@example.com',
            'password_hash' => 'password123',
            'age' => 40
        ]);
        
        // Username should be limited to 20 characters and use underscores
        $this->assertTrue(strlen($dto->username) <= 20);
        $this->assertStringStartsWith('jean_françois', $dto->username);
    }
    
    public function test_password_hashing(): void
    {
        $dto = new TransformationExampleDTO([
            'title' => 'Test Article',
            'email' => 'test@example.com',
            'password_hash' => 'plainpassword',
            'age' => 25
        ]);
        
        // Password should be hashed (not equal to original)
        $this->assertNotEquals('plainpassword', $dto->password_hash);
        $this->assertEquals(64, strlen($dto->password_hash)); // SHA256 = 64 chars
    }
    
    public function test_complete_transformation_chain(): void
    {
        $dto = new TransformationExampleDTO([
            'title' => '  Guide Complet Laravel Arc  ',
            'first_name' => '  Marie  ',
            'last_name' => '  Dupont  ',
            'email' => '  MARIE.DUPONT@EXAMPLE.COM  ',
            'country_code' => '  fr  ',
            'password_hash' => 'supersecret123',
            'phone' => '  +33 6 12 34 56 78  ',
            'age' => 32,
            'bio' => '  Experte en développement web  '
        ]);
        
        // Verify all transformations worked correctly
        $this->assertEquals('Guide Complet Laravel Arc', $dto->title);
        $this->assertEquals('guide-complet-laravel-arc', $dto->slug);
        $this->assertEquals('Marie', $dto->first_name);
        $this->assertEquals('Dupont', $dto->last_name);
        $this->assertEquals('Marie Dupont', $dto->full_name);
        $this->assertEquals('marie_dupont', $dto->username);
        $this->assertEquals('marie.dupont@example.com', $dto->email);
        $this->assertEquals('FR', $dto->country_code);
        $this->assertEquals('+33 6 12 34 56 78', $dto->phone);
        $this->assertEquals('Experte en développement web', $dto->bio);
    }
}

