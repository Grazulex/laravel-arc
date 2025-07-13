<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Generator\Headers\TraitsHeaderGenerator;
use Grazulex\LaravelArc\Support\Traits\ConvertsData;
use Grazulex\LaravelArc\Support\Traits\DtoUtilities;
use Grazulex\LaravelArc\Support\Traits\ValidatesData;

/**
 * Modern DTO Generator - Trait-Based Architecture
 *
 * This generator enforces the new architecture where:
 * 1. The 3 functional traits are ALWAYS included (ValidatesData, ConvertsData, DtoUtilities)
 * 2. Behavioral traits are only added if declared in YAML
 * 3. No base class extension by default (only if 'extends' is explicitly set)
 * 4. Field-level transformers replace most option-based logic
 */
final class ModernDtoGeneratorCorrected
{
    /**
     * Core functional traits that are ALWAYS included in every DTO
     */
    private const ALWAYS_INCLUDED_TRAITS = [
        ValidatesData::class,
        ConvertsData::class,
        DtoUtilities::class,
    ];

    private TraitsHeaderGenerator $traitsHeaderGenerator;

    public function __construct()
    {
        $this->traitsHeaderGenerator = new TraitsHeaderGenerator();
    }

    /**
     * Generate DTO from modern YAML definition
     */
    public function generate(array $yamlData): string
    {
        $namespace = $yamlData['namespace'] ?? 'App\\DTO';
        $className = $yamlData['class_name'] ?? 'GeneratedDto';
        $extendsClass = $yamlData['extends'] ?? null; // No default extension
        $behavioralTraits = $yamlData['traits'] ?? [];
        $fields = $yamlData['fields'] ?? [];

        // Build the complete traits list
        $allTraits = $this->buildTraitsList($behavioralTraits);

        // Generate traits content using the TraitsHeaderGenerator
        $traitsContent = $this->traitsHeaderGenerator->generate('traits', ['traits' => $allTraits], new DtoGenerationContext());

        // Parse the traits content to extract use statements and trait uses
        if ($traitsContent === '' || $traitsContent === '0') {
            $traitUseStatements = '';
            $traitUseInClass = '';
        } else {
            $traitContentParts = explode("\n\n", $traitsContent);
            $traitUseStatements = $traitContentParts[0];
            $traitUseInClass = $traitContentParts[1] ?? '';
        }

        // Build class declaration
        $classDeclaration = $this->buildClassDeclaration($className, $extendsClass);

        // Generate fields and methods
        $properties = $this->generateProperties($fields);
        $constructor = $this->generateConstructor($fields);
        $getters = $this->generateGetters($fields);

        return $this->buildClassFile(
            $namespace,
            $traitUseStatements,
            $classDeclaration,
            $traitUseInClass,
            $properties,
            $constructor,
            $getters
        );
    }

    /**
     * Build the complete list of traits (functional + behavioral)
     */
    private function buildTraitsList(array $behavioralTraits): array
    {
        $allTraits = self::ALWAYS_INCLUDED_TRAITS;

        // Add behavioral traits with proper namespace resolution
        foreach ($behavioralTraits as $trait) {
            $fullTraitName = $this->resolveTraitNamespace($trait);
            if (! in_array($fullTraitName, $allTraits)) {
                $allTraits[] = $fullTraitName;
            }
        }

        return $allTraits;
    }

    /**
     * Resolve trait namespace (behavioral traits are in LaravelArc\Support\Traits\Behavioral)
     */
    private function resolveTraitNamespace(string $trait): string
    {
        // If already fully qualified, return as-is
        if (str_contains($trait, '\\')) {
            return $trait;
        }

        // Assume behavioral traits are in the Behavioral namespace
        return "LaravelArc\\Support\\Traits\\Behavioral\\{$trait}";
    }

    /**
     * Build class declaration (with or without extends)
     */
    private function buildClassDeclaration(string $className, ?string $extendsClass): string
    {
        $declaration = "class {$className}";

        if ($extendsClass !== null && $extendsClass !== '' && $extendsClass !== '0') {
            $declaration .= " extends {$extendsClass}";
        }

        return $declaration;
    }

    /**
     * Generate properties from fields
     */
    private function generateProperties(array $fields): string
    {
        $properties = [];

        foreach ($fields as $fieldName => $fieldConfig) {
            $type = $fieldConfig['type'] ?? 'mixed';
            $required = $fieldConfig['required'] ?? false;
            $description = $fieldConfig['description'] ?? '';

            $phpType = $this->mapTypeToPhp($type, $required);

            $properties[] = '    /**';
            if ($description) {
                $properties[] = "     * {$description}";
            }
            $properties[] = "     * @var {$phpType}";
            $properties[] = '     */';
            $properties[] = "    public {$phpType} \${$fieldName};";
            $properties[] = '';
        }

        return implode("\n", $properties);
    }

    /**
     * Map YAML types to PHP types
     */
    private function mapTypeToPhp(string $type, bool $required): string
    {
        $phpType = match ($type) {
            'string' => 'string',
            'integer', 'int' => 'int',
            'float', 'double' => 'float',
            'boolean', 'bool' => 'bool',
            'array' => 'array',
            'object' => 'object',
            default => 'mixed',
        };

        return $required ? $phpType : "?{$phpType}";
    }

    /**
     * Generate constructor
     */
    private function generateConstructor(array $fields): string
    {
        $parameters = [];
        $assignments = [];

        foreach ($fields as $fieldName => $fieldConfig) {
            $type = $fieldConfig['type'] ?? 'mixed';
            $required = $fieldConfig['required'] ?? false;
            $phpType = $this->mapTypeToPhp($type, $required);

            $default = $required ? '' : ' = null';
            $parameters[] = "        {$phpType} \${$fieldName}{$default}";
            $assignments[] = "        \$this->{$fieldName} = \${$fieldName};";
        }

        $constructor = "    public function __construct(\n";
        $constructor .= implode(",\n", $parameters)."\n";
        $constructor .= "    ) {\n";
        $constructor .= implode("\n", $assignments)."\n";

        return $constructor.'    }';
    }

    /**
     * Generate getter methods
     */
    private function generateGetters(array $fields): string
    {
        $getters = [];

        foreach ($fields as $fieldName => $fieldConfig) {
            $type = $fieldConfig['type'] ?? 'mixed';
            $required = $fieldConfig['required'] ?? false;
            $phpType = $this->mapTypeToPhp($type, $required);
            $methodName = 'get'.ucfirst($fieldName);

            $getters[] = "    public function {$methodName}(): {$phpType}";
            $getters[] = '    {';
            $getters[] = "        return \$this->{$fieldName};";
            $getters[] = '    }';
            $getters[] = '';
        }

        return implode("\n", $getters);
    }

    /**
     * Build the complete class file
     */
    private function buildClassFile(
        string $namespace,
        string $traitUseStatements,
        string $classDeclaration,
        string $traitUseInClass,
        string $properties,
        string $constructor,
        string $getters
    ): string {
        return <<<PHP
<?php

namespace {$namespace};

{$traitUseStatements}

/**
 * Generated DTO using Modern Trait-Based Architecture
 * 
 * This DTO always includes the 3 functional traits:
 * - ValidatesData: Provides validation capabilities
 * - ConvertsData: Provides data conversion and casting
 * - DtoUtilities: Provides utility methods (toArray, toJson, etc.)
 * 
 * Additional behavioral traits are included as declared in YAML.
 * By default, this DTO does NOT extend any base class.
 */
{$classDeclaration}
{
{$traitUseInClass}

{$properties}
{$constructor}

{$getters}
}
PHP;
    }
}
