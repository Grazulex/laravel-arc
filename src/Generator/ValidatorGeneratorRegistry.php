<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use InvalidArgumentException;

final class ValidatorGeneratorRegistry
{
    /** @var ValidatorGenerator[] */
    private array $generators = [];

    public function __construct(array $generators)
    {
        foreach ($generators as $generator) {
            if (! $generator instanceof ValidatorGenerator) {
                throw new InvalidArgumentException('Each generator must implement ValidatorGenerator.');
            }

            $this->generators[] = $generator;
        }
    }

    public function generate(string $name, string $type, array $config): ?string
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($type)) {
                return $generator->generate($name, $config);
            }
        }

        return null;
    }
}
