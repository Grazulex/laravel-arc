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
    private array $generators;

    public function __construct(array $generators)
    {
        foreach ($generators as $generator) {
            if (! $generator instanceof HeaderGenerator) {
                throw new InvalidArgumentException('Each generator must implement HeaderGenerator.');
            }
        }

        $this->generators = $generators;
    }

    public function generate(string $key, array $header, DtoGenerationContext $context): string
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($key)) {
                return $generator->generate($key, $header, $context);
            }
        }

        return '';
    }

    public function generateAll(array $yaml, DtoGenerationContext $context): array
    {
        $header = $yaml['header'] ?? [];
        $result = [];

        foreach ($header as $key => $_) {
            // Défensive : ignorer les clés non-string
            if (! is_string($key)) {
                continue;
            }

            foreach ($this->generators as $generator) {
                if ($generator->supports($key)) {
                    $result[$key] = $generator->generate($key, $header, $context);
                }
            }
        }

        return $result;
    }
}
