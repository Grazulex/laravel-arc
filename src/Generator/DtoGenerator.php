<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

final class DtoGenerator
{
    public function __construct(
        private HeaderGeneratorRegistry $headers,
        private FieldGeneratorRegistry $fields,
        private RelationGeneratorRegistry $relations,
    ) {}

    public static function make(): self
    {
        $context = new DtoGenerationContext();

        return new self(
            $context->headers(),
            $context->fields(),
            $context->relations(),
            $context->validators(),
        );
    }

    // Exemple de méthode future
    public function generateFromDefinition(array $yaml): string
    {
        $class = $this->headers->generate('dto', $yaml['header'] ?? []);
        $properties = [];

        foreach ($yaml['fields'] ?? [] as $name => $def) {
            $properties[] = $this->fields->generate($name, $def);
        }

        foreach ($yaml['relations'] ?? [] as $name => $def) {
            $relationCode = $this->relations->generate($name, $def);
            if ($relationCode !== null && $relationCode !== '' && $relationCode !== '0') {
                $properties[] = $relationCode;
            }
        }

        return $class."\n".implode("\n\n", $properties);
    }
}
