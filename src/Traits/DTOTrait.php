<?php

namespace Grazulex\Arc\Traits;

use function array_key_exists;

use BadMethodCallException;
use Carbon\Carbon;

use function count;
use function get_class;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Casting\CastManager;
use Grazulex\Arc\Contracts\DTOInterface;
use Grazulex\Arc\Contracts\TransformerInterface as LegacyTransformerInterface;
use Grazulex\Arc\Exceptions\InvalidDTOException;
use Grazulex\Arc\Interfaces\TransformerInterface;
use Grazulex\Arc\Transformation\TransformationManager;

use function in_array;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;

use ReflectionClass;
use ReflectionProperty;

use function strlen;

trait DTOTrait
{
    /**
     * The DTO's attributes.
     *
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * Cache for reflection properties.
     *
     * @var array<string, array<string, mixed>>
     */
    private static array $propertiesCache = [];

    /**
     * Magic method to get property values directly.
     */
    public function __get(string $name): mixed
    {
        // First try to get from actual class property if it exists and is initialized
        if (property_exists($this, $name)) {
            $reflection = new ReflectionProperty($this, $name);
            if ($reflection->isInitialized($this)) {
                return $this->{$name};
            }

            // If property is not initialized, check if it's nullable
            $type = $reflection->getType();
            if ($type && $type->allowsNull()) {
                return null;
            }
        }

        return $this->get($name);
    }

    /**
     * Magic method to set property values directly.
     */
    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    /**
     * Magic method to check if a property exists.
     */
    public function __isset(string $name): bool
    {
        // Check both actual property and attributes array
        if (property_exists($this, $name)) {
            $reflection = new ReflectionProperty($this, $name);
            if ($reflection->isInitialized($this)) {
                return true;
            }
        }

        return $this->has($name);
    }

    /**
     * Magic method to handle dynamic getters and setters (optionnel pour compatibilité).
     *
     * @param array<int, mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        // Handle getters (getName, getEmail, etc.) - optionnel, pour compatibilité
        if (str_starts_with($method, 'get') && strlen($method) > 3) {
            $property = lcfirst(substr($method, 3));

            return $this->get($property);
        }

        // Handle setters (setName, setEmail, etc.) - optionnel, pour compatibilité
        if (str_starts_with($method, 'set') && strlen($method) > 3 && count($arguments) === 1) {
            $property = lcfirst(substr($method, 3));
            $this->set($property, $arguments[0]);

            return $this;
        }

        throw new BadMethodCallException("Method {$method} does not exist.");
    }

    /**
     * Get an attribute from the DTO.
     */
    public function get(string $key): mixed
    {
        // First try to get from actual class property if it exists and is initialized
        if (property_exists($this, $key)) {
            $reflection = new ReflectionProperty($this, $key);
            if ($reflection->isInitialized($this)) {
                return $this->{$key};
            }
        }

        if (!array_key_exists($key, $this->attributes)) {
            $properties = $this->getReflectionProperties();
            if (isset($properties[$key])) {
                $attribute = $properties[$key]['attribute'];

                return $attribute?->default;
            }
        }

        return $this->attributes[$key] ?? null;
    }

    /**
     * Set an attribute in the DTO with type checking and casting.
     */
    public function set(string $key, mixed $value): static
    {
        $properties = $this->getReflectionProperties();

        if (isset($properties[$key])) {
            $attribute = $properties[$key]['attribute'];
            $reflectionProperty = $properties[$key]['property'];

            // Apply transformations first (before casting)
            if ($attribute && !empty($attribute->transform)) {
                // Build context from current DTO state for cross-field transformations
                $context = $this->buildTransformationContext();

                if (TransformationManager::shouldTransform($value, $attribute->transform, $context)) {
                    $value = TransformationManager::transform($value, $attribute->transform, $context);
                }
            }

            // Apply casting if specified
            if ($attribute && $attribute->cast) {
                $value = CastManager::cast($value, $attribute);
            }

            // Type checking - allow compatible types for dates
            if ($attribute && !$this->isValidType($value, $attribute->type, $reflectionProperty)) {
                throw InvalidDTOException::forTypeError($key, $attribute->type, $value);
            }

            // Set the actual class property if it exists - handle different Carbon types
            if (property_exists($this, $key)) {
                $propertyType = $reflectionProperty->getType();
                if ($propertyType && $propertyType->getName() === 'Carbon\CarbonImmutable' && $value instanceof Carbon) {
                    // Convert Carbon to CarbonImmutable if property expects it
                    $this->{$key} = $value->toImmutable();
                } else {
                    $this->{$key} = $value;
                }
            }
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Check if the DTO has an attribute.
     */
    public function has(string $key): bool
    {
        // Check actual property first
        if (property_exists($this, $key)) {
            $reflection = new ReflectionProperty($this, $key);
            if ($reflection->isInitialized($this)) {
                return true;
            }
        }

        return array_key_exists($key, $this->attributes);
    }

    /**
     * Convert the DTO to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = [];
        $properties = $this->getReflectionProperties();

        // If we have properties with attributes, use them
        if (!empty($properties)) {
            // Get values from actual properties if they exist and are initialized
            foreach ($properties as $name => $propertyData) {
                $value = null;
                $hasValue = false;

                if (property_exists($this, $name)) {
                    $reflection = new ReflectionProperty($this, $name);
                    if ($reflection->isInitialized($this)) {
                        $value = $this->{$name};
                        $hasValue = true;
                    }
                }

                // Fallback to attributes array
                if (!$hasValue && array_key_exists($name, $this->attributes)) {
                    $value = $this->attributes[$name];
                    $hasValue = true;
                }

                // Use default value if available or handle nullable properties
                if (!$hasValue) {
                    $attribute = $propertyData['attribute'];
                    $reflection = $propertyData['property'];

                    if ($attribute?->default !== null) {
                        $value = $attribute->default;
                        $hasValue = true;
                    } elseif (!$attribute->required || ($reflection->getType() && $reflection->getType()->allowsNull())) {
                        // Property is nullable or not required, set as null
                        $value = null;
                        $hasValue = true;
                    }
                }

                // Apply serialization if we have a value and casting is defined
                if ($hasValue) {
                    $attribute = $propertyData['attribute'];
                    if ($attribute && $attribute->cast && $value !== null) {
                        $value = CastManager::serialize($value, $attribute);
                    }
                    // Si la valeur est un DTO, on appelle sa méthode toArray()
                    if ($value instanceof DTOInterface) {
                        $value = $value->toArray();
                    }
                    $result[$name] = $value;
                }
            }
        } else {
            // If no properties with attributes, just return the attributes array
            $result = $this->attributes;
        }

        return $result;
    }

    /**
     * Convert the DTO to JSON.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Set an attribute without applying context-aware transformations (first pass).
     */
    public function setWithoutContextualTransforms(string $key, mixed $value): static
    {
        $properties = $this->getReflectionProperties();

        if (isset($properties[$key])) {
            $attribute = $properties[$key]['attribute'];
            $reflectionProperty = $properties[$key]['property'];

            // Apply only basic transformations (not context-aware ones)
            if ($attribute && !empty($attribute->transform)) {
                $basicTransformers = $this->filterBasicTransformers($attribute->transform);
                if (!empty($basicTransformers)) {
                    $context = $this->buildTransformationContext();
                    if (TransformationManager::shouldTransform($value, $basicTransformers, $context)) {
                        $value = TransformationManager::transform($value, $basicTransformers, $context);
                    }
                }
            }

            // Apply casting if specified
            if ($attribute && $attribute->cast) {
                $value = CastManager::cast($value, $attribute);
            }

            // Type checking
            if ($attribute && !$this->isValidType($value, $attribute->type, $reflectionProperty)) {
                throw InvalidDTOException::forTypeError($key, $attribute->type, $value);
            }

            // Set the actual class property if it exists
            if (property_exists($this, $key)) {
                $propertyType = $reflectionProperty->getType();
                if ($propertyType && $propertyType->getName() === 'Carbon\CarbonImmutable' && $value instanceof Carbon) {
                    $this->{$key} = $value->toImmutable();
                } else {
                    $this->{$key} = $value;
                }
            }
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Apply context-aware transformations in a second pass.
     *
     * @param array<string, mixed> $originalData
     */
    public function applyContextualTransformations(array $originalData): void
    {
        $properties = $this->getReflectionProperties();

        // Process ALL properties with contextual transformers, not just those in originalData
        foreach ($properties as $key => $propertyData) {
            $attribute = $propertyData['attribute'];
            $reflectionProperty = $propertyData['property'];

            if ($attribute && !empty($attribute->transform)) {
                $contextualTransformers = $this->filterContextualTransformers($attribute->transform);
                if (!empty($contextualTransformers)) {
                    $context = $this->buildTransformationContext();

                    // Start with current value (could be null for auto-generated fields)
                    $currentValue = $this->get($key);

                    if (TransformationManager::shouldTransform($currentValue, $contextualTransformers, $context)) {
                        $transformedValue = TransformationManager::transform($currentValue, $contextualTransformers, $context);

                        // Apply casting if specified
                        if ($attribute->cast) {
                            $transformedValue = CastManager::cast($transformedValue, $attribute);
                        }

                        // Type checking
                        if (!$this->isValidType($transformedValue, $attribute->type, $reflectionProperty)) {
                            throw InvalidDTOException::forTypeError($key, $attribute->type, $transformedValue);
                        }

                        // Update the property
                        if (property_exists($this, $key)) {
                            $propertyType = $reflectionProperty->getType();
                            if ($propertyType && $propertyType->getName() === 'Carbon\CarbonImmutable' && $transformedValue instanceof Carbon) {
                                $this->{$key} = $transformedValue->toImmutable();
                            } else {
                                $this->{$key} = $transformedValue;
                            }
                        }

                        $this->attributes[$key] = $transformedValue;
                    }
                }
            }
        }
    }

    /**
     * Filter transformers to get only basic (non-context-aware) ones.
     *
     * @param array<LegacyTransformerInterface|string|TransformerInterface> $transformers
     *
     * @return array<LegacyTransformerInterface|string|TransformerInterface>
     */
    private function filterBasicTransformers(array $transformers): array
    {
        return array_filter($transformers, function ($transformer) {
            // Check if this is a context-aware transformer
            return !$this->isContextualTransformer($transformer);
        });
    }

    /**
     * Filter transformers to get only context-aware ones.
     *
     * @param array<LegacyTransformerInterface|string|TransformerInterface> $transformers
     *
     * @return array<LegacyTransformerInterface|string|TransformerInterface>
     */
    private function filterContextualTransformers(array $transformers): array
    {
        return array_filter($transformers, function ($transformer) {
            return $this->isContextualTransformer($transformer);
        });
    }

    /**
     * Check if a transformer is context-aware (depends on other fields).
     */
    private function isContextualTransformer(LegacyTransformerInterface|string|TransformerInterface $transformer): bool
    {
        // List of known context-aware transformers
        $contextualTransformerClasses = [
            'Grazulex\Arc\Transformers\SlugTransformer',
            'Grazulex\Arc\Examples\TitleToSlugTransformer',
            'Grazulex\Arc\Examples\UsernameTransformer',
            'Grazulex\Arc\Examples\FullNameTransformer',
        ];

        if (is_string($transformer)) {
            return in_array($transformer, $contextualTransformerClasses, true);
            // DEBUG: Uncomment this for debugging
            // error_log("[DEBUG] Checking transformer string: $transformer, isContextual: " . ($isContextual ? 'true' : 'false'));
        }

        $transformerClass = get_class($transformer);

        return in_array($transformerClass, $contextualTransformerClasses, true);
        // DEBUG: Uncomment this for debugging
        // error_log("[DEBUG] Checking transformer class: $transformerClass, isContextual: " . ($isContextual ? 'true' : 'false'));
    }

    /**
     * Validate if a value matches the expected type.
     */
    private function isValidType(mixed $value, string $expectedType, ReflectionProperty $property): bool
    {
        // If value is null, check if property type allows null
        if ($value === null) {
            return $property->getType()?->allowsNull() ?? true;
        }

        // Check if it's a nested/relation type
        if (in_array($expectedType, ['nested', 'dto', 'relation'], true)) {
            return $value instanceof DTOInterface || is_array($value);
        }

        return match ($expectedType) {
            'string' => is_string($value),
            'int', 'integer' => is_int($value),
            'float', 'double' => is_float($value) || is_int($value),
            'bool', 'boolean' => is_bool($value),
            'array' => is_array($value),
            'object' => is_object($value),
            'enum' => is_object($value), // Enums are objects in PHP
            default => true // Pour les types custom ou mixed
        };
    }

    /**
     * Get reflection properties with their attributes.
     *
     * @return array<string, array<string, mixed>>
     */
    private function getReflectionProperties(): array
    {
        $class = static::class;

        if (!isset(self::$propertiesCache[$class])) {
            $reflection = new ReflectionClass($class);
            $properties = [];

            foreach ($reflection->getProperties() as $property) {
                // Check for Property attributes only
                $propertyAttributes = $property->getAttributes(Property::class);

                if (!empty($propertyAttributes)) {
                    $attribute = $propertyAttributes[0]->newInstance();
                    $properties[$property->getName()] = [
                        'property' => $property,
                        'attribute' => $attribute,
                    ];
                }
            }

            self::$propertiesCache[$class] = $properties;
        }

        return self::$propertiesCache[$class];
    }

    /**
     * Build transformation context from current DTO state.
     *
     * @return array<string, mixed>
     */
    private function buildTransformationContext(): array
    {
        $context = [];
        $properties = $this->getReflectionProperties();

        // Include all current property values in context
        foreach ($properties as $name => $propertyData) {
            // Try to get from actual property first
            if (property_exists($this, $name)) {
                $reflection = new ReflectionProperty($this, $name);
                if ($reflection->isInitialized($this)) {
                    $context[$name] = $this->{$name};
                    continue;
                }
            }

            // Fallback to attributes array
            if (array_key_exists($name, $this->attributes)) {
                $context[$name] = $this->attributes[$name];
            }
        }

        return $context;
    }
}
