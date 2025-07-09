<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

final class DtoGenerator
{
    public function __construct(
        // private HeaderGeneratorRegistry $headers,
        // private FieldGeneratorRegistry $fields,
        private RelationGeneratorRegistry $relations,
        private ValidatorGeneratorRegistry $validators,
        private OptionGeneratorRegistry $options,
    ) {}

    public static function make(): self
    {
        $context = new DtoGenerationContext();

        return new self(
            // $context->headers(),
            // $context->fields(),
            $context->relations(),
            $context->validators(),
            $context->options(),
        );
    }

    public function generateFromDefinition(array $yaml): string
    {
        $header = $yaml['header'] ?? [];

        $namespace = $header['namespace'] ?? 'App\\DTO';
        $className = $header['class'] ?? 'UnnamedDto';
        $modelFQCN = '\\'.mb_ltrim($header['model'] ?? 'App\\Models\\Model', '\\');

        $fields = $yaml['fields'] ?? [];

        $methods = [];
        $rules = [];

        // Validation rules for fields
        foreach ($fields as $name => $def) {
            $fieldRules = $this->validators->generate($name, $def['type'] ?? 'string', $def);
            if ($fieldRules !== []) {
                $rules = array_merge($rules, $fieldRules);
            }
        }

        // Relations (methods or properties)
        foreach ($yaml['relations'] ?? [] as $name => $def) {
            $relationCode = $this->relations->generate($name, $def);
            if ($relationCode !== null && $relationCode !== '' && $relationCode !== '0') {
                $methods[] = $relationCode;
            }
        }

        // Options (methods or traits)
        foreach ($yaml['options'] ?? [] as $name => $def) {
            $optionCode = $this->options->generate($name, $def);
            if ($optionCode !== null && $optionCode !== '' && $optionCode !== '0') {
                $methods[] = $optionCode;
            }
        }

        // Generate rules() and validate() methods
        if ($rules !== []) {
            $lines = [];
            foreach ($rules as $field => $ruleSet) {
                $joined = implode("', '", $ruleSet);
                $lines[] = "        '{$field}' => ['{$joined}'],";
            }

            $rulesCode = implode("\n", $lines);

            $methods[] = <<<PHP
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
        }

        // Generate class using template system
        $renderer = new DtoTemplateRenderer();

        $baseDto = $renderer->renderFullDto(
            $namespace,
            $className,
            $fields,
            $modelFQCN
        );

        // Append all extra methods
        if ($methods !== []) {
            return mb_rtrim($baseDto, "}\n")."\n\n".implode("\n\n", $methods)."\n}";
        }

        return $baseDto;
    }
}
