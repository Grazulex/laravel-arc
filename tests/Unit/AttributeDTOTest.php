<?php

use Grazulex\Arc\Exceptions\InvalidDTOException;
use Grazulex\Arc\LaravelArcDTO;

// DTO with attributes but without declared properties
class AttributeTestDTO extends LaravelArcDTO
{
    // Simulate rule generation for tests
    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
            'role' => 'nullable|string',
        ];
    }

    // Override validation to avoid Laravel
    protected function validate(array $data): void
    {
        if (empty($data['name'])) {
            throw new InvalidDTOException('Name is required');
        }
        if (isset($data['age']) && !is_int($data['age'])) {
            throw new InvalidDTOException('Age must be an integer');
        }
    }
}

describe('AttributeDTO', function () {
    it('can create DTO and access properties directly', function () {
        $dto = new AttributeTestDTO([
            'name' => 'Jean-Marc',
            'age' => 30,
            'role' => 'admin',
        ]);

        expect($dto->name)->toBe('Jean-Marc');
        expect($dto->age)->toBe(30);
        expect($dto->role)->toBe('admin');
    });

    it('can modify properties directly', function () {
        $dto = new AttributeTestDTO(['name' => 'Test', 'age' => 25]);

        expect($dto->name)->toBe('Test');
        expect($dto->age)->toBe(25);

        $dto->name = 'New name';
        $dto->age = 35;
        $dto->role = 'manager';

        expect($dto->name)->toBe('New name');
        expect($dto->age)->toBe(35);
        expect($dto->role)->toBe('manager');
    });

    it('is compatible with getters and setters', function () {
        $dto = new AttributeTestDTO(['name' => 'Test', 'age' => 25]);

        expect($dto->getName())->toBe('Test');

        $dto->setAge(40);

        expect($dto->age)->toBe(40);
    });

    it('can convert to array with pretty JSON', function () {
        $dto = new AttributeTestDTO([
            'name' => 'John Doe',
            'age' => 30,
            'role' => 'developer',
        ]);

        $array = $dto->toArray();
        expect($array)->toBeArray();
        expect($array['name'])->toBe('John Doe');
        expect($array['age'])->toBe(30);
        expect($array['role'])->toBe('developer');
    });

    it('can generate validation rules', function () {
        $rules = AttributeTestDTO::rules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey('name');
        expect($rules)->toHaveKey('age');
        expect($rules)->toHaveKey('role');
        expect($rules['name'])->toBe('required|string|max:255');
    });

    it('validates empty name correctly', function () {
        expect(function () {
            new AttributeTestDTO([
                'name' => '', // Empty
                'age' => 'not an integer',
            ]);
        })->toThrow(InvalidDTOException::class, 'Name is required');
    });

    it('validates integer age correctly', function () {
        expect(function () {
            new AttributeTestDTO([
                'name' => 'Test',
                'age' => 'not an integer',
            ]);
        })->toThrow(InvalidDTOException::class, 'Age must be an integer');
    });
});
