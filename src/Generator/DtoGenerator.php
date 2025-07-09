<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

final class DtoGenerator
{
    public function __construct(
        private HeaderGeneratorRegistry $headers,
        private FieldGeneratorRegistry $fields,
        private RelationGeneratorRegistry $relations,
        private ValidatorGeneratorRegistry $validators,
        private OptionGeneratorRegistry $options,
    ) {}

    public static function make(): self
    {
        $context = new DtoGenerationContext();

        return new self(
            $context->headers(),
            $context->fields(),
            $context->relations(),
            $context->validators(),
            $context->options(),
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

        // Process validators if present
        foreach ($yaml['validators'] ?? [] as $name => $def) {
            $validatorCode = $this->validators->generate($name, $def['type'] ?? 'default', $def);
            if ($validatorCode !== null && $validatorCode !== '' && $validatorCode !== '0') {
                $properties[] = $validatorCode;
            }
        }

        // Process options if present
        foreach ($yaml['options'] ?? [] as $name => $def) {
            $optionCode = $this->options->generate($name, $def);
            if ($optionCode !== null && $optionCode !== '' && $optionCode !== '0') {
                $properties[] = $optionCode;
            }
        }

        return $class."\n".implode("\n\n", $properties);
    }
}
