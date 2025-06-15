<?php

namespace Grazulex\Arc\Attributes;

use Attribute;
use BackedEnum;

use function class_exists;

use Grazulex\Arc\Contracts\TransformerInterface;

use function is_subclass_of;

use ReflectionClass;

use function str_contains;

use UnitEnum;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Property
{
    public ?string $class = null;
    public readonly ?string $cast;
    public readonly ?string $nested;
    public readonly bool $isCollection;
    public readonly ?string $format;
    public readonly ?string $timezone;
    public readonly bool $immutable;
    /** @var array<string|TransformerInterface> */
    public readonly array $transform;

    /**
     * @param array<class-string> $transform
     */
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
        ?string $class = null,
        // @var array<string|TransformerInterface> $transform
        array $transform = [],
    ) {
        // Smart cast detection based on type
        $this->cast = $cast ?? $this->detectCastType($type, $class);
        $this->nested = $this->detectNestedClass($type, $class);
        $this->isCollection = $collection || $this->detectCollection($type);
        $this->format = $format;
        $this->timezone = $timezone;
        $this->immutable = $immutable;
        $this->transform = $transform;
    }

    /**
     * Detect the appropriate cast type based on the property type.
     */
    private function detectCastType(string $type, ?string $class): string
    {
        // Handle explicit type declarations
        switch ($type) {
            case 'enum':
                return 'enum';
            case 'date':
            case 'datetime':
                return 'date';
            case 'nested':
                return 'nested';
            case 'collection':
                return 'nested'; // Collections are handled as nested with isCollection=true
            case 'string':
                return 'string';
            case 'int':
            case 'integer':
                return 'int';
            case 'float':
            case 'double':
                return 'float';
            case 'bool':
            case 'boolean':
                return 'bool';
            case 'array':
                return 'array';
        }

        // If a class is specified, try to auto-detect type
        if ($class) {
            if ($this->isEnumClass($class)) {
                return 'enum';
            }
            if ($this->isDTOClass($class)) {
                return 'nested';
            }
        }

        // Default fallback
        return 'string';
    }

    /**
     * Detect the nested class for enum or DTO casting.
     */
    private function detectNestedClass(string $type, ?string $class): ?string
    {
        // Return explicit class parameter
        return $class;
    }

    /**
     * Detect if this is a collection type.
     */
    private function detectCollection(string $type): bool
    {
        return $type === 'collection';
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
