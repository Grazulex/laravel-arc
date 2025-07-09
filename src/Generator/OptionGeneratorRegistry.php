<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Contracts\OptionGenerator;
use InvalidArgumentException;

final class OptionGeneratorRegistry
{
    /**
     * @var array<string, OptionGenerator>
     */
    private array $generators = [];

    public function __construct(array $generators)
    {
        foreach ($generators as $type => $generator) {
            if (! $generator instanceof OptionGenerator) {
                throw new InvalidArgumentException('Each option generator must implement OptionGenerator.');
            }

            $this->generators[$type] = $generator;
        }
    }

    public function generate(string $type, mixed $value): ?string
    {
        if (! isset($this->generators[$type])) {
            return null;
        }

        return $this->generators[$type]->generate($value);
    }
}
