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

    public function generateFromDefinition(array $yaml): string
    {
        $class = $this->headers->generate('dto', $yaml['header'] ?? []);

        $properties = [];
        $rules = [];

        // Fields
        foreach ($yaml['fields'] ?? [] as $name => $def) {
            $properties[] = $this->fields->generate($name, $def);

            $fieldRules = $this->validators->generate($name, $def['type'] ?? 'string', $def);
            if ($fieldRules !== []) {
                $rules = array_merge($rules, $fieldRules);
            }
        }

        // Relations
        foreach ($yaml['relations'] ?? [] as $name => $def) {
            $relationCode = $this->relations->generate($name, $def);
            if ($relationCode !== null && $relationCode !== '') {
                $properties[] = $relationCode;
            }
        }

        // Options
        foreach ($yaml['options'] ?? [] as $name => $def) {
            $optionCode = $this->options->generate($name, $def);
            if ($optionCode !== null && $optionCode !== '') {
                $properties[] = $optionCode;
            }
        }

        // Validation rules (rules() and validate())
        if ($rules !== []) {
            $lines = [];
            foreach ($rules as $field => $ruleSet) {
                $joined = implode("', '", $ruleSet);
                $lines[] = "        '{$field}' => ['{$joined}'],";
            }

            $rulesCode = implode("\n", $lines);

            $rulesMethod = <<<PHP
    public static function rules(): array
    {
        return [
    $rulesCode
        ];
    }
    
    public static function validate(array \$data): \Illuminate\Contracts\Validation\Validator
    {
        return \Illuminate\Support\Facades\Validator::make(\$data, static::rules());
    }
    PHP;

            $properties[] = $rulesMethod;
        }

        return $class."\n\n".implode("\n\n", $properties);
    }
}
