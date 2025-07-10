<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Contracts\OptionGenerator;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class OptionGeneratorRegistry
{
    /**
     * @var array<string, OptionGenerator>
     */
    private array $generators = [];

    public function __construct(array $generators)
    {
        foreach ($generators as $generator) {
            if (! $generator instanceof OptionGenerator) {
                throw new InvalidArgumentException('Each generator must implement OptionGenerator');
            }

            $key = (string) Str::of(class_basename($generator))
                ->before('OptionGenerator')
                ->camel(); // ex: SoftDeletesOptionGenerator => "softDeletes"

            $this->generators[$key] = $generator;
        }

    }

    public function generate(string $type, mixed $value): ?string
    {
        $type = Str::camel($type);
        if (! isset($this->generators[$type])) {
            return null;
        }

        return $this->generators[$type]->generate($value);
    }
}
