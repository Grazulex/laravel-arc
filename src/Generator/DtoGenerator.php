<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Exception;
use Grazulex\LaravelArc\Exceptions\DtoGenerationException;
use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;

final class DtoGenerator
{
    public function __construct(
        private HeaderGeneratorRegistry $headers,
        private FieldGeneratorRegistry $fields,
        private RelationGeneratorRegistry $relations,
        private ValidatorGeneratorRegistry $validators,
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

    public function generateFromDefinition(array $yaml, ?string $yamlFile = null): string
    {
        try {
            $context = new DtoGenerationContext();

            $header = $yaml['header'] ?? [];
            $fieldDefinitions = $yaml['fields'] ?? [];
            $relationDefinitions = $yaml['relations'] ?? [];

            // Extract traits from top level or header
            $traits = $yaml['traits'] ?? $header['traits'] ?? [];

            // Expand trait fields if traits are defined
            if (! empty($traits)) {
                $fieldDefinitions = $this->expandTraitFields($fieldDefinitions, $traits);
            }

            // Extract namespace and class from top level or header
            $namespace = $yaml['namespace'] ?? $header['namespace'] ?? 'App\\DTO';

            // Merge top-level YAML values into header for compatibility with generators
            $mergedHeader = array_merge($header, [
                'namespace' => $namespace,
                'class' => $yaml['class'] ?? $header['class'] ?? $header['dto'] ?? 'UnnamedDto',
                'model_fqcn' => $yaml['model_fqcn'] ?? $header['model_fqcn'] ?? '\App\Models\Model',
                'traits' => $traits,
            ]);

            $className = $this->headers->generate('dto', $mergedHeader, $context);
            $modelFQCN = $this->headers->generate('model', $mergedHeader, $context);
            $this->headers->generate('table', $mergedHeader, $context);

            // --- Collect header extras ---
            $headerExtras = [];
            $useStatements = $this->headers->generate('use', $mergedHeader, $context);
            if ($useStatements !== '' && $useStatements !== '0') {
                $headerExtras[] = $useStatements;
            }

            // Generate trait use statements
            if (! empty($traits)) {
                $traitStatements = $this->headers->generate('traits', $mergedHeader, $context);
                if ($traitStatements !== '' && $traitStatements !== '0') {
                    $headerExtras[] = $traitStatements;
                }
            }

            $extendsClause = $this->headers->generate('extends', $mergedHeader, $context);

            $headerExtra = implode("\n", $headerExtras);
            if ($headerExtra !== '' && $headerExtra !== '0') {
                $headerExtra .= "\n";
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

            // Add trait validation rules
            if (! empty($traits)) {
                $traitRules = BehavioralTraitRegistry::getValidationRulesForTraits($traits);
                $allRules = array_merge($allRules, $traitRules);
            }

            // NOTE: Trait methods are available via 'use TraitName;' statements
            // No need to generate them inline - this follows PHP trait architecture

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
PHP;
            }

            // --- Generate behavioral trait use statements for class body ---
            $behavioralTraitUses = $this->generateBehavioralTraitUses($traits);

            // --- Render DTO class ---
            return (new DtoTemplateRenderer())->renderFullDtoWithRenderedProperties(
                $namespace,
                $className,
                $renderedProperties, // utiliser les propriétés pré-rendues
                $fieldDefinitions, // garder les définitions pour fromModel/toArray
                $modelFQCN,
                $methods,
                $headerExtra,
                $extendsClause,
                $behavioralTraitUses
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

    /**
     * Expand trait fields into the field definitions.
     *
     * @param  array<string, mixed>  $fieldDefinitions
     * @param  array<string>  $traits
     * @return array<string, mixed>
     */
    private function expandTraitFields(array $fieldDefinitions, array $traits): array
    {
        return BehavioralTraitRegistry::expandFields($fieldDefinitions, $traits);
    }

    /**
     * Generate behavioral trait use statements for the class body.
     *
     * @param  array<string>  $traits
     */
    private function generateBehavioralTraitUses(array $traits): string
    {
        if ($traits === []) {
            return '';
        }

        $useStatements = [];
        foreach ($traits as $traitName) {
            $useStatements[] = "    use {$traitName};";
        }

        return "\n".implode("\n", $useStatements);
    }
}
