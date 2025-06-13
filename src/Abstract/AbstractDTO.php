<?php

namespace Grazulex\Arc\Abstract;

use Grazulex\Arc\Contracts\DTOInterface;
use Grazulex\Arc\Traits\DTOTrait;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Exceptions\InvalidDTOException;
use ReflectionClass;

abstract class AbstractDTO implements DTOInterface
{
    use DTOTrait;

    /**
     * Create a new DTO instance.
     */
    public function __construct(array $attributes = [])
    {
        $this->validateAndSet($attributes);
    }

    /**
     * Create a new DTO instance from an array.
     */
    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    /**
     * Get the validation rules for the DTO (automatically generated).
     */
    public static function rules(): array
    {
        $reflection = new ReflectionClass(static::class);
        $rules = [];
        
        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(Property::class);
            if (!empty($attributes)) {
                $attribute = $attributes[0]->newInstance();
                $propertyName = $property->getName();
                
                $rule = [];
                
                // Required/optional
                if ($attribute->required) {
                    $rule[] = 'required';
                } else {
                    $rule[] = 'nullable';
                }
                
                // Type validation
                $rule[] = match ($attribute->type) {
                    'string' => 'string',
                    'int', 'integer' => 'integer',
                    'float', 'double' => 'numeric',
                    'bool', 'boolean' => 'boolean',
                    'array' => 'array',
                    default => 'string' // Default fallback
                };
                
                // Custom validation if provided
                if ($attribute->validation) {
                    $rule[] = $attribute->validation;
                }
                
                $rules[$propertyName] = implode('|', $rule);
            }
        }
        
        return $rules;
    }

    /**
     * Validate and set data
     */
    protected function validateAndSet(array $data): void
    {
        $this->validate($data);
        
        // Set attributes with type checking
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
        
        // Check for required properties and set defaults
        $this->setDefaultsForMissingRequired();
    }

    /**
     * Validate the data against the DTO's rules.
     */
    protected function validate(array $data): void
    {
        $validator = validator($data, static::rules());

        if ($validator->fails()) {
            throw InvalidDTOException::forValidationErrors($validator->errors()->toArray());
        }
    }
    
    /**
     * Set default values for missing required properties
     */
    private function setDefaultsForMissingRequired(): void
    {
        $properties = $this->getReflectionProperties();
        
        foreach ($properties as $name => $propertyData) {
            if (!$this->has($name)) {
                $attribute = $propertyData['attribute'];
                if ($attribute->default !== null) {
                    // Set both the class property and attributes array
                    if (property_exists($this, $name)) {
                        $this->$name = $attribute->default;
                    }
                    $this->attributes[$name] = $attribute->default;
                }
            }
        }
    }
}

