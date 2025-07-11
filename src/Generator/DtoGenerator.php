<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Contracts\FieldExpandingOptionGenerator;

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

    public function generateFromDefinition(array $yaml): string
    {
        $context = new DtoGenerationContext();

        $header = $yaml['header'] ?? [];
        $fieldDefinitions = $yaml['fields'] ?? [];
        $relationDefinitions = $yaml['relations'] ?? [];
        $optionDefinitions = $yaml['options'] ?? [];

        $namespace = $header['namespace'] ?? 'App\\DTO';

        $className = $this->headers->generate('dto', $header, $context);
        $modelFQCN = $this->headers->generate('model', $header, $context);
        $this->headers->generate('table', $header, $context);

        // --- Collect header extras ---
        $headerExtras = [];
        $useStatements = $this->headers->generate('use', $header, $context);
        if ($useStatements !== '' && $useStatements !== '0') {
            $headerExtras[] = $useStatements;
        }

        $extendsClause = $this->headers->generate('extends', $header, $context);

        $headerExtra = implode("\n", $headerExtras);
        if ($headerExtra !== '' && $headerExtra !== '0') {
            $headerExtra .= "\n";
        }

        // --- Inject extra fields from options ---
        foreach ($optionDefinitions as $name => $value) {
            $generator = $this->options->get($name);

            if (! $generator instanceof \Grazulex\LaravelArc\Contracts\OptionGenerator) {
                continue; // skip unsupported option
            }

            if ($generator instanceof FieldExpandingOptionGenerator) {
                $extraFields = $generator->expandFields($value);
                foreach ($extraFields as $key => $fieldDef) {
                    $fieldDefinitions[$key] = $fieldDef; // safe override
                }
            }
        }

        // --- Generate rendered properties ---
        $renderedProperties = [];
        foreach ($fieldDefinitions as $name => $def) {
            $renderedProperties[$name] = $this->fields->generate($name, $def);
        }

        // --- Generate relation methods ---
        $methods = [];
        foreach ($relationDefinitions as $name => $def) {
            $code = $this->relations->generate($name, $def);
            if ($code !== null && $code !== '' && $code !== '0') {
                $methods[] = $code;
            }
        }

        // --- Generate option methods (non-field-expanding) ---
        foreach ($optionDefinitions as $name => $value) {
            $generator = $this->options->get($name);
            $code = $generator?->generate($name, $value, $context);

            if ($code !== null && $code !== '' && $code !== '0') {
                $methods[] = $code;
            }
        }

        // --- Generate validation rules ---
        $allRules = [];
        foreach ($fieldDefinitions as $name => $def) {
            foreach ($this->validators->generate($name, $def) as $field => $rules) {
                $allRules[$field] = $rules;
            }
        }

        if ($allRules !== []) {
            $rulesBody = implode("\n", array_map(
                fn ($field, $rules): string => "        '{$field}' => ['".implode("', '", $rules)."'],",
                array_keys($allRules),
                $allRules
            ));

            $methods[] = <<<PHP
            public static function rules(): array
            {
                return [
                    $rulesBody
                ];
            }

            public static function validate(array \$data): \\Illuminate\\Contracts\\Validation\\Validator
            {
                return \\Illuminate\\Support\\Facades\\Validator::make(\$data, static::rules());
            }
            PHP;
        }

        // --- Render DTO class ---
        return (new DtoTemplateRenderer())->renderFullDto(
            $namespace,
            $className,
            $fieldDefinitions, // important: includes both base and expanded
            $modelFQCN,
            $methods,
            $headerExtra,
            $extendsClause
        );
    }
}
