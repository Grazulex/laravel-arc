<?php

namespace Grazulex\Arc\Casting\Casters;

use Exception;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Casting\BaseCaster;
use Grazulex\Arc\Contracts\DTOInterface;
use Grazulex\Arc\Exceptions\InvalidDTOException;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

use function is_array;

/**
 * Handles casting values to and from nested DTOs.
 */
class NestedCaster extends BaseCaster
{
    protected function getSupportedCastTypes(): array
    {
        return ['nested', 'dto', 'relation'];
    }

    /**
     * @return array<DTOInterface>|DTOInterface
     */
    protected function performCast(mixed $value, Property $attribute): null|array|DTOInterface
    {
        if ($value === null) {
            if ($attribute->required) {
                throw InvalidDTOException::forCastingError('nested', $value, 'Value cannot be null for required nested property');
            }

            return null;
        }

        // Get the DTO class from either nested or class property
        $dtoClass = $attribute->nested ?? $attribute->class;

        if (!$dtoClass) {
            throw new InvalidArgumentException('DTO class not specified');
        }

        if (!class_exists($dtoClass)) {
            throw InvalidDTOException::forCastingError('nested', $value, "Class {$dtoClass} does not exist");
        }

        if (!is_subclass_of($dtoClass, DTOInterface::class)) {
            throw InvalidDTOException::forCastingError('nested', $value, "Class {$dtoClass} must implement DTOInterface");
        }

        try {
            // Handle collections
            if ($attribute->isCollection) {
                return $this->castToCollection($value, $dtoClass);
            }

            // Handle single nested DTO
            return $this->castToSingleDto($value, $dtoClass);
        } catch (Exception $e) {
            throw InvalidDTOException::forCastingError('nested', $value, $e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function performSerialization(mixed $value, Property $attribute): array
    {
        if ($attribute->isCollection) {
            return $this->serializeCollection($value);
        }

        return $this->serializeSingleDto($value);
    }

    /**
     * Cast value to a collection of DTOs.
     *
     * @param class-string<DTOInterface> $dtoClass
     *
     * @return array<DTOInterface>
     */
    private function castToCollection(mixed $value, string $dtoClass): array
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('Value must be an array for collection');
        }

        return array_map(function ($item) use ($dtoClass) {
            if ($item instanceof $dtoClass) {
                return $item;
            }

            return new $dtoClass(is_array($item) ? $item : []);
        }, $value);
    }

    /**
     * Cast value to a single DTO.
     *
     * @param class-string<DTOInterface> $dtoClass
     */
    private function castToSingleDto(mixed $value, string $dtoClass): DTOInterface
    {
        if ($value instanceof $dtoClass) {
            return $value;
        }

        if ($value instanceof Model) {
            // Convert model to array and use fromArray
            return $dtoClass::fromArray($value->toArray());
        }

        if (is_array($value)) {
            // Si le tableau contient déjà une instance du DTO
            if (isset($value[$dtoClass]) && is_array($value[$dtoClass])) {
                $value = $value[$dtoClass];
            }

            try {
                // Utilise la méthode fromArray du DTO si elle existe
                if (method_exists($dtoClass, 'fromArray')) {
                    return $dtoClass::fromArray($value);
                }

                // Sinon, crée une nouvelle instance avec les données
                return new $dtoClass($value);
            } catch (Exception $e) {
                throw $e;
            }
        }

        throw new InvalidArgumentException('Value must be an array, Model, or instance of ' . $dtoClass);
    }

    /**
     * Serialize a collection of DTOs.
     *
     * @return array<string, mixed>
     */
    private function serializeCollection(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_map(function ($item) {
            if ($item instanceof DTOInterface) {
                return $item->toArray();
            }

            return $item;
        }, $value);
    }

    /**
     * Serialize a single DTO.
     *
     * @return array<string, mixed>
     */
    private function serializeSingleDto(mixed $value): array
    {
        if ($value instanceof DTOInterface) {
            return $value->toArray();
        }

        if (is_array($value) && isset($value['class'])) {
            return $value['class'];
        }

        return is_array($value) ? $value : [];
    }
}
