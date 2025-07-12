<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Exception;
use Grazulex\LaravelArc\Contracts\FieldGenerator;
use Grazulex\LaravelArc\Exceptions\DtoGenerationException;
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
            throw DtoGenerationException::unsupportedFieldType('', $name, $type);
        }

        try {
            return $this->generators[$type]->generate($name, $definition, $this->context);
        } catch (DtoGenerationException $e) {
            // Re-throw existing DtoGenerationException
            throw $e;
        } catch (Exception $e) {
            throw DtoGenerationException::invalidField(
                '',
                $name,
                "Field generation failed: {$e->getMessage()}",
                null
            );
        }
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
