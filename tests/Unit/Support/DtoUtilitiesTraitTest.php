<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\Traits\DtoUtilities;

// Test DTO class using DtoUtilities trait
final class UtilityTestDto
{
    use DtoUtilities;

    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $status = 'active'
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
        ];
    }
}

describe('DtoUtilities Trait', function () {
    beforeEach(function () {
        $this->dto = new UtilityTestDto(1, 'John Doe', 'john@example.com', 'active');
    });

    it('gets all property names', function () {
        $properties = $this->dto->getProperties();

        expect($properties)->toBeArray();
        expect($properties)->toContain('id');
        expect($properties)->toContain('name');
        expect($properties)->toContain('email');
        expect($properties)->toContain('status');
    });

    it('checks if property exists', function () {
        expect($this->dto->hasProperty('id'))->toBe(true);
        expect($this->dto->hasProperty('name'))->toBe(true);
        expect($this->dto->hasProperty('nonexistent'))->toBe(false);
    });

    it('gets property value', function () {
        expect($this->dto->getProperty('id'))->toBe(1);
        expect($this->dto->getProperty('name'))->toBe('John Doe');
        expect($this->dto->getProperty('email'))->toBe('john@example.com');
    });

    it('throws exception for nonexistent property', function () {
        expect(fn () => $this->dto->getProperty('nonexistent'))
            ->toThrow(InvalidArgumentException::class, "Property 'nonexistent' does not exist");
    });

    it('creates new instance with modified properties', function () {
        $newDto = $this->dto->with(['name' => 'Jane Doe', 'status' => 'inactive']);

        expect($newDto)->toBeInstanceOf(UtilityTestDto::class);
        expect($newDto->id)->toBe(1); // unchanged
        expect($newDto->name)->toBe('Jane Doe'); // changed
        expect($newDto->email)->toBe('john@example.com'); // unchanged
        expect($newDto->status)->toBe('inactive'); // changed

        // Original should be unchanged
        expect($this->dto->name)->toBe('John Doe');
        expect($this->dto->status)->toBe('active');
    });

    it('compares DTOs for equality', function () {
        $sameDto = new UtilityTestDto(1, 'John Doe', 'john@example.com', 'active');
        $differentDto = new UtilityTestDto(2, 'Jane Doe', 'jane@example.com', 'inactive');

        expect($this->dto->equals($sameDto))->toBe(true);
        expect($this->dto->equals($differentDto))->toBe(false);
    });

    it('compares DTOs with same data but different instances', function () {
        $sameDataDto = new UtilityTestDto(1, 'John Doe', 'john@example.com', 'active');

        expect($this->dto->equals($sameDataDto))->toBe(true);
        expect($this->dto === $sameDataDto)->toBe(false); // Different instances
    });

    it('handles DTOs with nullable properties', function () {
        $dtoWithNull = new UtilityTestDto(1, 'John Doe', 'john@example.com');

        expect($dtoWithNull->hasProperty('status'))->toBe(true);
        expect($dtoWithNull->getProperty('status'))->toBe('active'); // Default value
    });
});
