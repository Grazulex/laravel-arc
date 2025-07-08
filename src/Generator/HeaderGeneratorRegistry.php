<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Contracts\HeaderGenerator;
use InvalidArgumentException;

final class HeaderGeneratorRegistry
{
    /**
     * @var HeaderGenerator[]
     */
    private array $generators = [];

    public function __construct(array $generators)
    {
        foreach ($generators as $generator) {
            if (! $generator instanceof HeaderGenerator) {
                throw new InvalidArgumentException('Each generator must implement HeaderGenerator.');
            }
        }

        $this->generators = $generators;
    }

    public function generateAll(array $yaml, string $dtoName): array
    {
        $result = [];

        foreach (array_keys($yaml) as $key) {
            foreach ($this->generators as $generator) {
                if ($generator->supports($key)) {
                    $result[$key] = $generator->generate($yaml, $dtoName);
                }
            }
        }

        return $result;
    }
}
