<?php

namespace Grazulex\Arc\Abstract;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Contracts\DTOInterface;
use Grazulex\Arc\Exceptions\InvalidDTOException;
use Grazulex\Arc\Traits\DTOTrait;

use function in_array;

use ReflectionClass;

abstract class AbstractDTO implements DTOInterface
{
    use DTOTrait;

    /**
     * Create a new DTO instance.
     *
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->validateAndSet($attributes);
    }

    /**
     * Create a new DTO instance from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    /**
     * Get the validation rules for the DTO (automatically generated).
     *
     * @return array<string, string>
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
                if (!in_array($attribute->type, ['nested', 'dto', 'relation'], true)) {
                    $rule[] = match ($attribute->type) {
                        'string' => 'string',
                        'int', 'integer' => 'integer',
                        'float', 'double' => 'numeric',
                        'bool', 'boolean' => 'boolean',
                        'array' => 'array',
                        default => 'string' // Default fallback
                    };
                }

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
     * Validate and set data.
     *
     * @param array<string, mixed> $data
     */
    protected function validateAndSet(array $data): void
    {
        // Only validate if data is provided - skip validation for empty constructor calls
        if (!empty($data)) {
            $this->validate($data);
        }

        // PASS 1: Set attributes with basic transformations only (no context-aware transformations)
        foreach ($data as $key => $value) {
            $this->setWithoutContextualTransforms($key, $value);
        }

        // Check for required properties and set defaults
        $this->setDefaultsForMissingRequired();

        // PASS 2: Re-apply context-aware transformations now that all values are set
        $this->applyContextualTransformations($data);
    }

    /**
     * Validate the data against the DTO's rules.
     *
     * @param array<string, mixed> $data
     */
    protected function validate(array $data): void
    {
        $rules = static::rules();

        $validator = validator($data, $rules);

        if ($validator->fails()) {
            throw InvalidDTOException::forValidationErrors($validator->errors()->toArray());
        }
    }

    /**
     * Set default values for missing required properties and initialize nullable properties.
     */
    private function setDefaultsForMissingRequired(): void
    {
        $properties = $this->getReflectionProperties();

        foreach ($properties as $name => $propertyData) {
            if (!$this->has($name)) {
                $attribute = $propertyData['attribute'];
                $property = $propertyData['property'];

                if ($attribute->default !== null) {
                    // Set both the class property and attributes array
                    if (property_exists($this, $name)) {
                        $this->{$name} = $attribute->default;
                    }
                    $this->attributes[$name] = $attribute->default;
                } elseif (!$attribute->required || ($property->getType() && $property->getType()->allowsNull())) {
                    // Initialize nullable or optional properties with null
                    if (property_exists($this, $name)) {
                        $this->{$name} = null;
                    }
                    $this->attributes[$name] = null;
                }
            }
        }
    }
}
