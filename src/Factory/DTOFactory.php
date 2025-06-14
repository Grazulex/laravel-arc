<?php

namespace Grazulex\Arc\Factory;

use function array_slice;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Grazulex\Arc\Attributes\DateProperty;
use Grazulex\Arc\Attributes\NestedProperty;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Contracts\DTOFactoryInterface;
use Grazulex\Arc\Contracts\DTOInterface;
use ReflectionClass;
use ReflectionProperty;

class DTOFactory implements DTOFactoryInterface
{
    /**
     * @var class-string<DTOInterface>
     */
    protected string $dtoClass;

    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * @var array<string, array{property: ReflectionProperty, attribute: Property}>
     */
    protected array $reflectionProperties = [];

    /**
     * @param class-string<DTOInterface> $dtoClass
     */
    public function __construct(string $dtoClass)
    {
        $this->dtoClass = $dtoClass;
        $this->loadReflectionProperties();
    }

    public function with(string $property, mixed $value): static
    {
        $this->attributes[$property] = $value;

        return $this;
    }

    public function withAttributes(array $attributes): static
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    public function fake(): static
    {
        foreach ($this->reflectionProperties as $name => $propertyData) {
            if (!isset($this->attributes[$name])) {
                $this->attributes[$name] = $this->generateFakeValue($propertyData['attribute']);
            }
        }

        return $this;
    }

    public function fakeOnly(array $properties): static
    {
        foreach ($properties as $property) {
            if (isset($this->reflectionProperties[$property])) {
                $this->attributes[$property] = $this->generateFakeValue(
                    $this->reflectionProperties[$property]['attribute'],
                );
            }
        }

        return $this;
    }

    public function create(): DTOInterface
    {
        return new ($this->dtoClass)($this->attributes);
    }

    public function createMany(int $count): array
    {
        $instances = [];

        for ($i = 0; $i < $count; ++$i) {
            // Create a new factory instance for each item to avoid data sharing
            $factory = new static($this->dtoClass);
            $factory->attributes = $this->attributes;
            $instances[] = $factory->fake()->create();
        }

        return $instances;
    }

    public function make(): DTOInterface
    {
        return $this->create();
    }

    public function makeMany(int $count): array
    {
        return $this->createMany($count);
    }

    /**
     * Load reflection properties for the DTO class.
     */
    protected function loadReflectionProperties(): void
    {
        $reflection = new ReflectionClass($this->dtoClass);

        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(Property::class);
            if (empty($attributes)) {
                $attributes = $property->getAttributes(DateProperty::class);
            }
            if (empty($attributes)) {
                $attributes = $property->getAttributes(NestedProperty::class);
            }

            if (!empty($attributes)) {
                $attribute = $attributes[0]->newInstance();
                $this->reflectionProperties[$property->getName()] = [
                    'property' => $property,
                    'attribute' => $attribute,
                ];
            }
        }
    }

    /**
     * Generate fake value based on property attribute.
     */
    protected function generateFakeValue(Property $attribute): mixed
    {
        // Si une valeur par défaut existe, l'utiliser pour les propriétés optionnelles
        if (!$attribute->required && $attribute->default !== null) {
            return $attribute->default;
        }

        // Gestion spéciale pour DateProperty
        if ($attribute instanceof DateProperty) {
            return $this->generateFakeDate($attribute);
        }

        // Gestion spéciale pour NestedProperty
        if ($attribute instanceof NestedProperty) {
            return $this->generateFakeNested($attribute);
        }

        // Génération basée sur le type
        return match ($attribute->type) {
            'string' => $this->generateFakeString($attribute),
            'int', 'integer' => $this->generateFakeInteger($attribute),
            'float', 'double' => $this->generateFakeFloat(),
            'bool', 'boolean' => $this->generateFakeBoolean(),
            'array' => $this->generateFakeArray(),
            default => null,
        };
    }

    /**
     * Generate fake date value.
     */
    protected function generateFakeDate(DateProperty $attribute): Carbon|CarbonImmutable
    {
        $fakeDate = Carbon::now()->subDays(rand(0, 365));

        if ($attribute->timezone) {
            $fakeDate->setTimezone($attribute->timezone);
        }

        return $attribute->immutable ? $fakeDate->toImmutable() : $fakeDate;
    }

    /**
     * Generate fake nested DTO.
     */
    protected function generateFakeNested(NestedProperty $attribute): mixed
    {
        if ($attribute->isCollection) {
            $count = rand(1, 3);
            $items = [];

            for ($i = 0; $i < $count; ++$i) {
                $factory = new static($attribute->dtoClass);
                $items[] = $factory->fake()->create();
            }

            return $items;
        }

        $factory = new static($attribute->dtoClass);

        return $factory->fake()->create();
    }

    /**
     * Generate fake string value.
     */
    protected function generateFakeString(Property $attribute): string
    {
        // Détection basée sur la validation
        if (str_contains($attribute->validation ?? '', 'email')) {
            return $this->generateFakeEmail();
        }

        // Génération par défaut avec plus de variété et identifiant unique
        $words = ['Lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit',
            'sed', 'do', 'eiusmod', 'tempor', 'incididunt', 'ut', 'labore', 'et',
            'dolore', 'magna', 'aliqua', 'enim', 'ad', 'minim', 'veniam', 'quis'];

        // Shuffle pour plus de randomisation
        shuffle($words);
        $wordCount = rand(1, 3);
        $baseText = implode(' ', array_slice($words, 0, $wordCount));

        // Ajouter un identifiant unique pour garantir l'unicité
        $uniqueId = substr(uniqid(), -4);

        return $baseText . ' ' . $uniqueId;
    }

    /**
     * Generate fake email.
     */
    protected function generateFakeEmail(): string
    {
        $names = ['john', 'jane', 'alice', 'bob', 'charlie', 'diana'];
        $domains = ['example.com', 'test.org', 'demo.net'];

        return $names[array_rand($names)] . rand(1, 999) . '@' . $domains[array_rand($domains)];
    }

    /**
     * Generate fake integer value.
     */
    protected function generateFakeInteger(Property $attribute): int
    {
        // Extraction des limites depuis la validation
        $min = 1;
        $max = 100;

        if ($attribute->validation) {
            if (preg_match('/min:(\d+)/', $attribute->validation, $matches)) {
                $min = (int) $matches[1];
            }
            if (preg_match('/max:(\d+)/', $attribute->validation, $matches)) {
                $max = (int) $matches[1];
            }
        }

        return rand($min, $max);
    }

    /**
     * Generate fake float value.
     */
    protected function generateFakeFloat(): float
    {
        return round(rand(1, 1000) / rand(1, 10), 2);
    }

    /**
     * Generate fake boolean value.
     */
    protected function generateFakeBoolean(): bool
    {
        return (bool) rand(0, 1);
    }

    /**
     * Generate fake array data.
     *
     * @return array<string>
     */
    protected function generateFakeArray(): array
    {
        $items = ['item1', 'item2', 'item3', 'item4'];
        $count = rand(1, 3);

        return array_slice($items, 0, $count);
    }
}
