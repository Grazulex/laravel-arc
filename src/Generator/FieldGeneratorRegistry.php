<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Contracts\FieldGenerator;
use InvalidArgumentException;

final class FieldGeneratorRegistry
{
    /**
     * @var array<string, FieldGenerator>
     */
    private array $generators = [];

    public function __construct(array $generators, private DtoGenerationContext $context)
    {
        foreach ($generators as $generator) {
            if (! $generator instanceof FieldGenerator) {
                throw new InvalidArgumentException('Each generator must implement FieldGenerator.');
            }

            foreach ($this->getSupportedTypes($generator) as $type) {
                $this->generators[$type] = $generator;
            }
        }
    }

    public function generate(string $name, array $definition): string
    {
        $type = $definition['type'] ?? 'string';

        if (! isset($this->generators[$type])) {
            throw new InvalidArgumentException("No generator found for field type '{$type}'");
        }

        return $this->generators[$type]->generate($name, $definition, $this->context);
    }

    /**
     * Get all supported types for a generator by testing known types.
     *
     * @return string[]
     */
    private function getSupportedTypes(FieldGenerator $generator): array
    {
        $knownTypes = [
            'string', 'text', 'integer', 'int', 'float', 'double',
            'enum',
            'decimal', 'boolean', 'bool', 'array', 'json',
            'datetime', 'date', 'time', 'uuid', 'dto',
        ];

        return array_filter($knownTypes, fn (string $type): bool => $generator->supports($type));
    }
}
