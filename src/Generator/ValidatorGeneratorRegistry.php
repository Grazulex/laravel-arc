<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use InvalidArgumentException;

final class ValidatorGeneratorRegistry
{
    /** @var ValidatorGenerator[] */
    private array $generators;

    public function __construct(array $generators, private DtoGenerationContext $context)
    {
        foreach ($generators as $generator) {
            if (! $generator instanceof ValidatorGenerator) {
                throw new InvalidArgumentException('Each generator must implement ValidatorGenerator.');
            }
        }

        $this->generators = $generators;
    }

    public function generate(string $name, array $definition): array
    {
        $type = $definition['type'] ?? null;

        foreach ($this->generators as $generator) {
            if ($type && $generator->supports($type)) {
                return $generator->generate($name, $definition, $this->context);
            }
        }

        return [];
    }
}
