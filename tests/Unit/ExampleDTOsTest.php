<?php

use Grazulex\Arc\Examples\ModernUserDTO as BaseModernUserDTO;
use Grazulex\Arc\Examples\UserDTO as BaseUserDTO;
use Grazulex\Arc\Exceptions\InvalidDTOException;

// Test versions that override validation for unit testing
class TestUserDTO extends BaseUserDTO
{
    protected function validate(array $data): void
    {
        // Simple validation for testing without Laravel dependencies
        if (empty($data['name'])) {
            throw new InvalidDTOException('Name is required');
        }
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidDTOException('Email is required and must be valid');
        }
        if (!isset($data['age']) || !is_int($data['age']) || $data['age'] < 0) {
            throw new InvalidDTOException('Age must be a positive integer');
        }
    }
}

class TestModernUserDTO extends BaseModernUserDTO
{
    protected function validate(array $data): void
    {
        // Simple validation for testing without Laravel dependencies
        if (empty($data['name'])) {
            throw new InvalidDTOException('Name is required');
        }
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidDTOException('Email is required and must be valid');
        }
        if (!isset($data['age']) || !is_int($data['age']) || $data['age'] < 0 || $data['age'] > 150) {
            throw new InvalidDTOException('Age must be between 0 and 150');
        }
    }
}

describe('UserDTO Example', static function () {
    it('can create a UserDTO with all required properties', static function () {
        $user = new TestUserDTO([
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'age' => 30,
        ]);

        expect($user->name)->toBe('John Doe');
        expect($user->email)->toBe('test@example.com');
        expect($user->age)->toBe(30);
    });

    it('can modify UserDTO properties directly', static function () {
        $user = new TestUserDTO([
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'age' => 30,
        ]);

        $user->name = 'Jane Doe';

        expect($user->name)->toBe('Jane Doe');
    });

    it('can use getters and setters on UserDTO', static function () {
        $user = new TestUserDTO([
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'age' => 30,
        ]);

        expect($user->getName())->toBe('John Doe');
        expect($user->getEmail())->toBe('test@example.com');
        expect($user->getAge())->toBe(30);

        $user->setName('Updated Name');
        $user->setAge(35);

        expect($user->name)->toBe('Updated Name');
        expect($user->age)->toBe(35);
    });

    it('can generate validation rules automatically', static function () {
        $rules = TestUserDTO::rules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey('name');
        expect($rules)->toHaveKey('email');
        expect($rules)->toHaveKey('age');
    });
});

describe('ModernUserDTO Example', static function () {
    it('can create ModernUserDTO with default values', static function () {
        $user = new TestModernUserDTO([
            'name' => 'Mary Smith',
            'email' => 'mary@example.com',
            'age' => 25,
        ]);

        expect($user->name)->toBe('Mary Smith');
        expect($user->email)->toBe('mary@example.com');
        expect($user->age)->toBe(25);
        expect($user->role)->toBe('user'); // Default value
        expect($user->active)->toBeTrue(); // Default value
        expect($user->permissions)->toBe([]); // Default value
    });

    it('can override default values', static function () {
        $user = new TestModernUserDTO([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'age' => 35,
            'role' => 'admin',
            'active' => false,
            'permissions' => ['read', 'write', 'delete'],
        ]);

        expect($user->role)->toBe('admin');
        expect($user->active)->toBeFalse();
        expect($user->permissions)->toBe(['read', 'write', 'delete']);
    });

    it('can modify all properties after creation', static function () {
        $user = new TestModernUserDTO([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'age' => 25,
        ]);

        // Modify via direct access
        $user->role = 'manager';
        $user->active = false;
        $user->permissions = ['read', 'write'];

        expect($user->role)->toBe('manager');
        expect($user->active)->toBeFalse();
        expect($user->permissions)->toBe(['read', 'write']);
    });

    it('can use automatic getters and setters', static function () {
        $user = new TestModernUserDTO([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'age' => 25,
        ]);

        // Test getters
        expect($user->getName())->toBe('Test User');
        expect($user->getEmail())->toBe('test@example.com');
        expect($user->getAge())->toBe(25);
        expect($user->getRole())->toBe('user');
        expect($user->getActive())->toBeTrue();
        expect($user->getPermissions())->toBe([]);

        // Test setters
        $user->setName('Updated User');
        $user->setRole('admin');
        $user->setPermissions(['admin']);

        expect($user->name)->toBe('Updated User');
        expect($user->role)->toBe('admin');
        expect($user->permissions)->toBe(['admin']);
    });

    it('can convert to array with all properties', static function () {
        $user = new TestModernUserDTO([
            'name' => 'Complete User',
            'email' => 'complete@example.com',
            'age' => 30,
            'role' => 'developer',
            'active' => true,
            'permissions' => ['read', 'write'],
        ]);

        $array = $user->toArray();

        expect($array)->toBeArray();
        expect($array['name'])->toBe('Complete User');
        expect($array['email'])->toBe('complete@example.com');
        expect($array['age'])->toBe(30);
        expect($array['role'])->toBe('developer');
        expect($array['active'])->toBeTrue();
        expect($array['permissions'])->toBe(['read', 'write']);
    });

    it('can generate comprehensive validation rules', static function () {
        $rules = TestModernUserDTO::rules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey('name');
        expect($rules)->toHaveKey('email');
        expect($rules)->toHaveKey('age');
        expect($rules)->toHaveKey('role');
        expect($rules)->toHaveKey('active');
        expect($rules)->toHaveKey('permissions');
    });
});
