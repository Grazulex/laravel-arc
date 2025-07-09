<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use InvalidArgumentException;

final class ValidatorGeneratorRegistry
{
    /**
     * @var array<ValidatorGenerator>
     */
    private array $generators;

    public function __construct(array $generators)
    {
        foreach ($generators as $generator) {
            if (! $generator instanceof ValidatorGenerator) {
                throw new InvalidArgumentException('Each generator must implement ValidatorGenerator.');
            }

            $this->generators[] = $generator;
        }
    }

    /**
     * @return array<string, array<string>> Laravel-style rules (e.g. ['email' => ['required', 'email']])
     */
    public function generate(string $name, string $type, array $config): array
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($type)) {
                return $generator->generate($name, $config);
            }
        }

        return [];
    }
}
