<?php

namespace Grazulex\Arc\Attributes;

use Attribute;
use BackedEnum;

use function class_exists;
use function interface_exists;
use function is_subclass_of;

use ReflectionClass;

use function str_contains;
use function str_starts_with;

use UnitEnum;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Property
{
    public readonly ?string $cast;
    public readonly ?string $nested;
    public readonly bool $isCollection;

    public function __construct(
        public readonly string $type,
        public readonly bool $required = true,
        public readonly mixed $default = null,
        public readonly ?string $validation = null,
        ?string $cast = null,
        ?string $format = null,
        ?string $timezone = null,
        bool $immutable = false,
        bool $collection = false,
        ?string $enumClass = null,
        ?string $dtoClass = null,
    ) {
        // Smart cast detection based on type
        $this->cast = $cast ?? $this->detectCastType($type, $enumClass, $dtoClass);
        $this->nested = $this->detectNestedClass($type, $enumClass, $dtoClass);
        $this->isCollection = $collection || $this->detectCollection($type);
    }

    /**
     * Automatically detect the appropriate cast type based on the property type.
     */
    private function detectCastType(string $type, ?string $enumClass, ?string $dtoClass): string
    {
        // Handle array types
        if (str_starts_with($type, 'array')) {
            if ($this->detectCollection($type)) {
                return 'nested'; // array<SomeDTO>
            }

            return 'array';
        }

        // Handle Carbon dates
        if ($type === 'Carbon' || $type === 'CarbonImmutable' || str_contains($type, 'Carbon')) {
            return 'date';
        }

        // Handle enums (explicit or by class check)
        if ($enumClass || $this->isEnumClass($type)) {
            return 'enum';
        }

        // Handle DTOs (explicit or by class check)
        if ($dtoClass || $this->isDTOClass($type)) {
            return 'nested';
        }

        // Handle basic PHP types
        return match ($type) {
            'string' => 'string',
            'int', 'integer' => 'int',
            'float', 'double' => 'float',
            'bool', 'boolean' => 'bool',
            'array' => 'array',
            default => 'string', // fallback
        };
    }

    /**
     * Detect the nested class for enum or DTO casting.
     */
    private function detectNestedClass(string $type, ?string $enumClass, ?string $dtoClass): ?string
    {
        if ($enumClass) {
            return $enumClass;
        }

        if ($dtoClass) {
            return $dtoClass;
        }

        // Extract class from array notation: array<UserDTO> -> UserDTO
        if (str_contains($type, '<') && str_contains($type, '>')) {
            preg_match('/array<(.+)>/', $type, $matches);
            if (isset($matches[1])) {
                return $matches[1];
            }
        }

        // Check if type itself is a class
        if (class_exists($type) || interface_exists($type)) {
            return $type;
        }

        return null;
    }

    /**
     * Detect if this is a collection type.
     */
    private function detectCollection(string $type): bool
    {
        return str_starts_with($type, 'array<') || str_contains($type, '[]');
    }

    /**
     * Check if a class is an enum.
     */
    private function isEnumClass(string $className): bool
    {
        if (!class_exists($className)) {
            return false;
        }

        return is_subclass_of($className, UnitEnum::class) || is_subclass_of($className, BackedEnum::class);
    }

    /**
     * Check if a class is likely a DTO.
     */
    private function isDTOClass(string $className): bool
    {
        if (!class_exists($className)) {
            return false;
        }

        // Check if class implements DTOInterface or uses DTOTrait
        $reflection = new ReflectionClass($className);

        // Check for DTOInterface
        if ($reflection->implementsInterface('Grazulex\\Arc\\Contracts\\DTOInterface')) {
            return true;
        }

        // Check for DTOTrait usage
        $traits = $reflection->getTraitNames();
        foreach ($traits as $trait) {
            if (str_contains($trait, 'DTOTrait')) {
                return true;
            }
        }

        return false;
    }
}
