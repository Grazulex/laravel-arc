<?php

use Grazulex\Arc\LaravelArcDTO;
use Grazulex\Arc\Exceptions\InvalidDTOException;

// DTO simple sans propriétés déclarées pour tester la logique de base
class BasicTestDTO extends LaravelArcDTO
{
    // Override la validation pour éviter Laravel
    protected function validate(array $data): void
    {
        if (empty($data['name'])) {
            throw new InvalidDTOException("Name is required");
        }
    }
}

describe('BasicDTO', function () {
    it('can create a DTO with basic functionality', function () {
        $dto = new BasicTestDTO([
            'name' => 'Jean-Marc',
            'age' => 30,
            'role' => 'admin'
        ]);
        
        expect($dto)->toBeInstanceOf(BasicTestDTO::class);
    });
    
    it('can access properties via get() method', function () {
        $dto = new BasicTestDTO(['name' => 'Test', 'age' => 25]);
        
        expect($dto->get('name'))->toBe('Test');
        expect($dto->get('age'))->toBe(25);
    });
    
    it('can modify properties via set() method', function () {
        $dto = new BasicTestDTO(['name' => 'Test', 'age' => 25]);
        
        expect($dto->get('name'))->toBe('Test');
        
        $dto->set('name', 'Nouveau nom');
        
        expect($dto->get('name'))->toBe('Nouveau nom');
    });
    
    it('can access properties via magic __get', function () {
        $dto = new BasicTestDTO(['name' => 'Test Magic', 'age' => 25]);
        
        expect($dto->name)->toBe('Test Magic');
        expect($dto->age)->toBe(25);
    });
    
    it('can modify properties via magic __set', function () {
        $dto = new BasicTestDTO(['name' => 'Test', 'age' => 25]);
        
        expect($dto->name)->toBe('Test');
        
        $dto->name = 'Modifié par magie';
        
        expect($dto->name)->toBe('Modifié par magie');
    });
    
    it('can convert to array and JSON', function () {
        $dto = new BasicTestDTO(['name' => 'Test', 'age' => 25, 'role' => 'user']);
        
        $array = $dto->toArray();
        expect($array)->toBeArray();
        expect($array['name'])->toBe('Test');
        expect($array['age'])->toBe(25);
        expect($array['role'])->toBe('user');
        
        $json = $dto->toJson();
        expect($json)->toBeString();
        expect(json_decode($json, true))->toBe($array);
    });
    
    it('validates required fields', function () {
        expect(function () {
            new BasicTestDTO(['name' => '']);
        })->toThrow(InvalidDTOException::class, 'Name is required');
    });
});

