<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Generator\Contracts\HeaderGeneratorContract;

final class HeaderGeneratorRegistry
{
    /**
     * @var HeaderGeneratorContract[]
     */
    private array $generators = [];

    public function __construct(iterable $generators)
    {
        foreach ($generators as $generator) {
            if ($generator instanceof HeaderGeneratorContract) {
                $this->generators[] = $generator;
            }
        }
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
