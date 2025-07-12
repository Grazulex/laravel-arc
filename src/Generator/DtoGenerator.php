<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Exception;
use Grazulex\LaravelArc\Contracts\FieldExpandingOptionGenerator;
use Grazulex\LaravelArc\Exceptions\DtoGenerationException;

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

    public function generateFromDefinition(array $yaml, ?string $yamlFile = null): string
    {
        try {
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
                try {
                    $renderedProperties[$name] = $this->fields->generate($name, $def);
                } catch (DtoGenerationException $e) {
                    // Re-throw with DTO and file context if not already set
                    if (in_array($e->getYamlFile(), [null, '', '0'], true) || in_array($e->getDtoName(), [null, '', '0'], true)) {
                        // Create a new exception with the same type but with proper context
                        $newException = match ($e->getCode()) {
                            1004 => DtoGenerationException::unsupportedFieldType($yamlFile ?? '', $name, $e->getFieldType() ?? 'unknown', $className),
                            1003 => DtoGenerationException::invalidField($yamlFile ?? '', $name, $e->getMessage(), $className),
                            default => DtoGenerationException::invalidField($yamlFile ?? '', $name, $e->getMessage(), $className),
                        };
                        throw $newException;
                    }
                    throw $e;
                } catch (Exception $e) {
                    if (str_contains($e->getMessage(), 'No generator found for field type')) {
                        // Extract field type from error message
                        preg_match("/field type '([^']+)'/", $e->getMessage(), $matches);
                        $fieldType = $matches[1] ?? 'unknown';
                        throw DtoGenerationException::unsupportedFieldType($yamlFile ?? '', $name, $fieldType, $className);
                    }
                    throw DtoGenerationException::invalidField(
                        $yamlFile ?? '',
                        $name,
                        $e->getMessage(),
                        $className
                    );
                }
            }

            // --- Generate relation methods ---
            $methods = [];
            foreach ($relationDefinitions as $name => $def) {
                try {
                    $code = $this->relations->generate($name, $def);
                    if ($code !== null && $code !== '' && $code !== '0') {
                        $methods[] = $code;
                    }
                } catch (Exception $e) {
                    throw DtoGenerationException::relationConfigurationError(
                        $yamlFile ?? '',
                        $name,
                        $e->getMessage(),
                        $className
                    );
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
                try {
                    foreach ($this->validators->generate($name, $def) as $field => $rules) {
                        $allRules[$field] = $rules;
                    }
                } catch (Exception $e) {
                    throw DtoGenerationException::validationRuleError(
                        $yamlFile ?? '',
                        $name,
                        $e->getMessage(),
                        $className
                    );
                }
            }

            if ($allRules !== []) {
                $rulesBody = implode("\n", array_map(
                    fn ($field, $rules): string => "            '{$field}' => ['".implode("', '", $rules)."'],",
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
            return (new DtoTemplateRenderer())->renderFullDtoWithRenderedProperties(
                $namespace,
                $className,
                $renderedProperties, // utiliser les propriétés pré-rendues
                $fieldDefinitions, // garder les définitions pour fromModel/toArray
                $modelFQCN,
                $methods,
                $headerExtra,
                $extendsClause
            );
        } catch (DtoGenerationException $e) {
            throw $e;
        } catch (Exception $e) {
            throw DtoGenerationException::invalidField(
                $yamlFile ?? '',
                '',
                "DTO generation failed: {$e->getMessage()}",
                null
            );
        }
    }
}
