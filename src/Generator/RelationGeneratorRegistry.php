<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Contracts\RelationGenerator;
use InvalidArgumentException;

final class RelationGeneratorRegistry
{
    /** @var RelationGenerator[] */
    private array $generators;

    public function __construct(array $generators, private DtoGenerationContext $context)
    {
        foreach ($generators as $generator) {
            if (! $generator instanceof RelationGenerator) {
                throw new InvalidArgumentException('Each generator must implement RelationGenerator.');
            }
        }

        $this->generators = $generators;
    }

    public function generate(string $name, array $definition): ?string
    {
        $type = $definition['type'] ?? null;

        foreach ($this->generators as $generator) {
            if ($type && $generator->supports($type)) {
                return $generator->generate($name, $definition, $this->context);
            }
        }

        return null;
    }
}
