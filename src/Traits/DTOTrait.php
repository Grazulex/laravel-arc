<?php

namespace Grazulex\Arc\Traits;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Exceptions\InvalidDTOException;
use ReflectionClass;
use ReflectionProperty;

trait DTOTrait
{
    /**
     * The DTO's attributes.
     */
    protected array $attributes = [];

    /**
     * Cache for reflection properties
     */
    private static array $propertiesCache = [];

    /**
     * Magic method to get property values directly
     */
    public function __get(string $name): mixed
    {
        // First try to get from actual class property if it exists and is initialized
        if (property_exists($this, $name)) {
            $reflection = new \ReflectionProperty($this, $name);
            if ($reflection->isInitialized($this)) {
                return $this->$name;
            }
        }
        return $this->get($name);
    }

    /**
     * Magic method to set property values directly
     */
    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    /**
     * Magic method to check if a property exists
     */
    public function __isset(string $name): bool
    {
        // Check both actual property and attributes array
        if (property_exists($this, $name)) {
            $reflection = new \ReflectionProperty($this, $name);
            if ($reflection->isInitialized($this)) {
                return true;
            }
        }
        return $this->has($name);
    }

    /**
     * Magic method to handle dynamic getters and setters (optionnel pour compatibilité)
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

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }

    /**
     * Get an attribute from the DTO.
     */
    public function get(string $key): mixed
    {
        // First try to get from actual class property if it exists and is initialized
        if (property_exists($this, $key)) {
            $reflection = new \ReflectionProperty($this, $key);
            if ($reflection->isInitialized($this)) {
                return $this->$key;
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
     * Set an attribute in the DTO with type checking
     */
    public function set(string $key, mixed $value): static
    {
        $properties = $this->getReflectionProperties();
        
        if (isset($properties[$key])) {
            $attribute = $properties[$key]['attribute'];
            $reflectionProperty = $properties[$key]['property'];
            
            // Type checking
            if ($attribute && !$this->isValidType($value, $attribute->type, $reflectionProperty)) {
                throw InvalidDTOException::forTypeError($key, $attribute->type, $value);
            }
            
            // Set the actual class property if it exists
            if (property_exists($this, $key)) {
                $this->$key = $value;
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
            $reflection = new \ReflectionProperty($this, $key);
            if ($reflection->isInitialized($this)) {
                return true;
            }
        }
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Convert the DTO to an array.
     */
    public function toArray(): array
    {
        $result = [];
        $properties = $this->getReflectionProperties();
        
        // If we have properties with attributes, use them
        if (!empty($properties)) {
            // Get values from actual properties if they exist and are initialized
            foreach ($properties as $name => $propertyData) {
                if (property_exists($this, $name)) {
                    $reflection = new \ReflectionProperty($this, $name);
                    if ($reflection->isInitialized($this)) {
                        $result[$name] = $this->$name;
                        continue;
                    }
                }
                
                // Fallback to attributes array
                if (array_key_exists($name, $this->attributes)) {
                    $result[$name] = $this->attributes[$name];
                } else {
                    // Use default value if available
                    $attribute = $propertyData['attribute'];
                    if ($attribute?->default !== null) {
                        $result[$name] = $attribute->default;
                    }
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
     * Get reflection properties with their attributes
     */
    private function getReflectionProperties(): array
    {
        $class = static::class;
        
        if (!isset(self::$propertiesCache[$class])) {
            $reflection = new ReflectionClass($class);
            $properties = [];
            
            foreach ($reflection->getProperties() as $property) {
                $attributes = $property->getAttributes(Property::class);
                if (!empty($attributes)) {
                    $attribute = $attributes[0]->newInstance();
                    $properties[$property->getName()] = [
                        'property' => $property,
                        'attribute' => $attribute
                    ];
                }
            }
            
            self::$propertiesCache[$class] = $properties;
        }
        
        return self::$propertiesCache[$class];
    }

    /**
     * Validate if a value matches the expected type
     */
    private function isValidType(mixed $value, string $expectedType, ReflectionProperty $property): bool
    {
        if ($value === null && !$property->getType()?->allowsNull()) {
            return false;
        }

        return match ($expectedType) {
            'string' => is_string($value),
            'int', 'integer' => is_int($value),
            'float', 'double' => is_float($value) || is_int($value),
            'bool', 'boolean' => is_bool($value),
            'array' => is_array($value),
            'object' => is_object($value),
            default => true // Pour les types custom ou mixed
        };
    }
}

