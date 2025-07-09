<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Contracts\FieldGenerator;
use InvalidArgumentException;

final class FieldGeneratorRegistry
{
    /** @var FieldGenerator[] */
    private array $generators;

    public function __construct(array $generators)
    {
        foreach ($generators as $generator) {
            if (! $generator instanceof FieldGenerator) {
                throw new InvalidArgumentException('Each generator must implement FieldGenerator.');
            }
        }

        $this->generators = $generators;
    }

    public function generate(string $name, array $definition): string
    {
        $type = $definition['type'] ?? throw new InvalidArgumentException("Missing 'type' for field '$name'");

        foreach ($this->generators as $generator) {
            if ($generator->supports($type)) {
                return $generator->generate($name, $definition);
            }
        }

        throw new InvalidArgumentException("No generator found for field type '$type'");
    }
}
