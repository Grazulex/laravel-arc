<?php

use Grazulex\Arc\LaravelArcDTO;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Exceptions\InvalidDTOException;

// Exemple réaliste d'un DTO Product
class ProductDTO extends LaravelArcDTO
{
    protected function validate(array $data): void
    {
        if (empty($data['name'])) {
            throw new InvalidDTOException("Product name is required");
        }
        if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] < 0) {
            throw new InvalidDTOException("Price must be a positive number");
        }
    }
}

// Exemple réaliste d'un DTO User
class UserDTO extends LaravelArcDTO
{
    protected function validate(array $data): void
    {
        if (empty($data['name'])) {
            throw new InvalidDTOException("User name is required");
        }
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidDTOException("Valid email is required");
        }
    }
}

describe('RealWorld Product Management', function () {
    it('can create and manage products', function () {
        $product = new ProductDTO([
            'name' => 'MacBook Pro',
            'price' => 2499.99,
            'category' => 'Electronics',
            'in_stock' => true,
            'tags' => ['apple', 'laptop', 'premium']
        ]);
        
        expect($product->name)->toBe('MacBook Pro');
        expect($product->price)->toBe(2499.99);
        expect($product->category)->toBe('Electronics');
        expect($product->in_stock)->toBeTrue();
        expect($product->tags)->toBe(['apple', 'laptop', 'premium']);
    });
    
    it('can apply promotions to products', function () {
        $product = new ProductDTO([
            'name' => 'MacBook Pro',
            'price' => 2499.99
        ]);
        
        expect($product->price)->toBe(2499.99);
        
        // Apply promotion
        $product->price = 2199.99;
        
        expect($product->price)->toBe(2199.99);
    });
    
    it('validates product name is required', function () {
        expect(function () {
            new ProductDTO([
                'name' => '',
                'price' => 2499.99
            ]);
        })->toThrow(InvalidDTOException::class, 'Product name is required');
    });
    
    it('validates product price must be positive', function () {
        expect(function () {
            new ProductDTO([
                'name' => 'Test Product',
                'price' => -50
            ]);
        })->toThrow(InvalidDTOException::class, 'Price must be a positive number');
    });
});

describe('RealWorld User Management', function () {
    it('can create and manage users', function () {
        $user = new UserDTO([
            'name' => 'Jean-Marc Strauven',
            'email' => 'jean-marc@example.com',
            'role' => 'developer',
            'active' => true,
            'last_login' => '2024-01-15'
        ]);
        
        expect($user->name)->toBe('Jean-Marc Strauven');
        expect($user->email)->toBe('jean-marc@example.com');
        expect($user->role)->toBe('developer');
        expect($user->active)->toBeTrue();
        expect($user->last_login)->toBe('2024-01-15');
    });
    
    it('can update user role and login time', function () {
        $user = new UserDTO([
            'name' => 'Jean-Marc Strauven',
            'email' => 'jean-marc@example.com',
            'role' => 'developer',
            'last_login' => '2024-01-15'
        ]);
        
        expect($user->role)->toBe('developer');
        
        $user->role = 'senior-developer';
        $user->last_login = '2024-01-16';
        
        expect($user->role)->toBe('senior-developer');
        expect($user->last_login)->toBe('2024-01-16');
    });
    
    it('validates user name is required', function () {
        expect(function () {
            new UserDTO([
                'name' => '',
                'email' => 'test@example.com'
            ]);
        })->toThrow(InvalidDTOException::class, 'User name is required');
    });
    
    it('validates email format', function () {
        expect(function () {
            new UserDTO([
                'name' => 'Test User',
                'email' => 'email-invalide'
            ]);
        })->toThrow(InvalidDTOException::class, 'Valid email is required');
    });
});

describe('Compatibility with getters and setters', function () {
    it('works with mixed access patterns', function () {
        $product = new ProductDTO([
            'name' => 'iPhone 15',
            'price' => 999.99
        ]);
        
        // Direct access
        expect($product->name)->toBe('iPhone 15');
        
        // Via getter
        expect($product->getPrice())->toBe(999.99);
        
        // Via setter
        $product->setName('iPhone 15 Pro');
        expect($product->name)->toBe('iPhone 15 Pro');
        
        // Direct modification
        $product->price = 1199.99;
        expect($product->price)->toBe(1199.99);
    });
});

describe('Conversion and serialization', function () {
    it('can convert complex user data to array and JSON', function () {
        $user = new UserDTO([
            'name' => 'Alice Dubois',
            'email' => 'alice@example.com',
            'role' => 'manager',
            'permissions' => ['read', 'write', 'delete'],
            'metadata' => [
                'department' => 'IT',
                'hire_date' => '2023-01-15'
            ]
        ]);
        
        $array = $user->toArray();
        
        expect($array)->toBeArray();
        expect($array['name'])->toBe('Alice Dubois');
        expect($array['email'])->toBe('alice@example.com');
        expect($array['role'])->toBe('manager');
        expect($array['permissions'])->toBe(['read', 'write', 'delete']);
        expect($array['metadata'])->toBe([
            'department' => 'IT',
            'hire_date' => '2023-01-15'
        ]);
        
        $json = $user->toJson();
        expect($json)->toBeString();
        expect(json_decode($json, true))->toBe($array);
    });
});

