<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Contracts\HeaderGenerator;
use InvalidArgumentException;

final class HeaderGeneratorRegistry
{
    /**
     * @var array<string, HeaderGenerator>
     */
    private array $generators = [];

    public function __construct(array $generators)
    {
        foreach ($generators as $key => $generator) {
            if (! $generator instanceof HeaderGenerator) {
                throw new InvalidArgumentException('Each generator must implement HeaderGenerator.');
            }

            $this->generators[$key] = $generator;
        }
    }

    public function generate(string $type, array $data): string
    {
        if (! isset($this->generators[$type])) {
            throw new InvalidArgumentException("Unknown header generator type: {$type}");
        }

        return $this->generators[$type]->generate($data, $type);
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
