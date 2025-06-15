<?php

namespace Grazulex\Arc\Commands;

use function count;

use Exception;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Contracts\DTOInterface;
use Illuminate\Console\Command;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class AnalyzeDtoCommand extends Command
{
    protected $signature = 'dto:analyze {class : The DTO class to analyze} {--json : Output as JSON}';

    protected $description = 'Analyze DTO structure and configuration';

    public function handle(): int
    {
        $className = $this->argument('class');
        $asJson = $this->option('json');

        try {
            $analysis = $this->analyzeDTO($className);

            if ($asJson) {
                $this->line(json_encode($analysis, JSON_PRETTY_PRINT));
            } else {
                $this->displayAnalysis($analysis);
            }

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error("Error analyzing DTO: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Analyze DTO class structure and configuration.
     *
     * @return array<string, mixed>
     */
    private function analyzeDTO(string $className): array
    {
        // Resolve class name
        $fullClassName = $this->resolveClassName($className);

        if (!class_exists($fullClassName)) {
            throw new Exception("Class {$fullClassName} does not exist");
        }

        $reflection = new ReflectionClass($fullClassName);

        $analysis = [
            'class' => $fullClassName,
            'is_dto' => $this->isDTOClass($reflection),
            'namespace' => $reflection->getNamespaceName(),
            'file' => $reflection->getFileName(),
            'properties' => [],
            'methods' => [],
            'interfaces' => $reflection->getInterfaceNames(),
            'traits' => $reflection->getTraitNames(),
            'parent' => $reflection->getParentClass() ? $reflection->getParentClass()->getName() : null,
            'statistics' => [
                'total_properties' => 0,
                'dto_properties' => 0,
                'required_properties' => 0,
                'nullable_properties' => 0,
                'properties_with_defaults' => 0,
                'properties_with_validation' => 0,
                'properties_with_transformations' => 0,
            ],
        ];

        // Analyze properties
        foreach ($reflection->getProperties() as $property) {
            $propertyAnalysis = $this->analyzeProperty($property);
            $analysis['properties'][$property->getName()] = $propertyAnalysis;

            // Update statistics
            ++$analysis['statistics']['total_properties'];

            if ($propertyAnalysis['has_dto_attribute']) {
                ++$analysis['statistics']['dto_properties'];

                if ($propertyAnalysis['attribute']['required']) {
                    ++$analysis['statistics']['required_properties'];
                }

                if ($propertyAnalysis['is_nullable']) {
                    ++$analysis['statistics']['nullable_properties'];
                }

                if ($propertyAnalysis['attribute']['default'] !== null) {
                    ++$analysis['statistics']['properties_with_defaults'];
                }

                if ($propertyAnalysis['attribute']['validation']) {
                    ++$analysis['statistics']['properties_with_validation'];
                }

                if (!empty($propertyAnalysis['attribute']['transform'])) {
                    ++$analysis['statistics']['properties_with_transformations'];
                }
            }
        }

        // Analyze methods
        foreach ($reflection->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() === $fullClassName) {
                $analysis['methods'][] = [
                    'name' => $method->getName(),
                    'visibility' => $this->getMethodVisibility($method),
                    'is_static' => $method->isStatic(),
                    'parameters' => count($method->getParameters()),
                    'return_type' => $method->getReturnType()?->__toString(),
                ];
            }
        }

        return $analysis;
    }

    /**
     * Analyze individual property.
     *
     * @return array<string, mixed>
     */
    private function analyzeProperty(ReflectionProperty $property): array
    {
        $attributes = $property->getAttributes(Property::class);
        $hasAttribute = !empty($attributes);

        $analysis = [
            'name' => $property->getName(),
            'visibility' => $this->getPropertyVisibility($property),
            'is_static' => $property->isStatic(),
            'has_dto_attribute' => $hasAttribute,
            'type' => $property->getType()?->__toString(),
            'is_nullable' => $property->getType()?->allowsNull() ?? false,
            'has_default' => $property->hasDefaultValue(),
            'default_value' => $property->hasDefaultValue() ? $property->getDefaultValue() : null,
        ];

        if ($hasAttribute) {
            $attribute = $attributes[0]->newInstance();
            $analysis['attribute'] = [
                'type' => $attribute->type,
                'required' => $attribute->required,
                'default' => $attribute->default,
                'validation' => $attribute->validation,
                'cast' => $attribute->cast,
                'nested' => $attribute->nested,
                'is_collection' => $attribute->isCollection,
                'format' => $attribute->format,
                'timezone' => $attribute->timezone,
                'immutable' => $attribute->immutable,
                'transform' => $attribute->transform,
            ];
        }

        return $analysis;
    }

    /**
     * Display analysis in human-readable format.
     *
     * @param array<string, mixed> $analysis
     */
    private function displayAnalysis(array $analysis): void
    {
        $this->info("📊 DTO Analysis: {$analysis['class']}");
        $this->line('');

        // Basic info
        $this->line("📁 File: {$analysis['file']}");
        $this->line("📦 Namespace: {$analysis['namespace']}");
        $this->line('🏷️  Is DTO: ' . ($analysis['is_dto'] ? '✅ Yes' : '❌ No'));

        if ($analysis['parent']) {
            $this->line("👨‍👩‍👧‍👦 Parent: {$analysis['parent']}");
        }

        if (!empty($analysis['interfaces'])) {
            $this->line('🔌 Interfaces: ' . implode(', ', $analysis['interfaces']));
        }

        if (!empty($analysis['traits'])) {
            $this->line('🧩 Traits: ' . implode(', ', $analysis['traits']));
        }

        $this->line('');

        // Statistics
        $stats = $analysis['statistics'];
        $this->info('📈 Statistics:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Properties', $stats['total_properties']],
                ['DTO Properties', $stats['dto_properties']],
                ['Required Properties', $stats['required_properties']],
                ['Nullable Properties', $stats['nullable_properties']],
                ['Properties with Defaults', $stats['properties_with_defaults']],
                ['Properties with Validation', $stats['properties_with_validation']],
                ['Properties with Transformations', $stats['properties_with_transformations']],
            ],
        );

        $this->line('');

        // Properties detail
        if (!empty($analysis['properties'])) {
            $this->info('🏷️  Properties:');
            $propertyRows = [];

            foreach ($analysis['properties'] as $name => $prop) {
                $flags = [];
                if ($prop['has_dto_attribute']) {
                    if ($prop['attribute']['required']) {
                        $flags[] = 'Required';
                    }
                    if ($prop['is_nullable']) {
                        $flags[] = 'Nullable';
                    }
                    if ($prop['attribute']['validation']) {
                        $flags[] = 'Validated';
                    }
                    if (!empty($prop['attribute']['transform'])) {
                        $flags[] = 'Transformed';
                    }
                }

                $propertyRows[] = [
                    $name,
                    $prop['type'] ?? 'mixed',
                    $prop['visibility'],
                    $prop['has_dto_attribute'] ? '✅' : '❌',
                    implode(', ', $flags),
                ];
            }

            $this->table(
                ['Name', 'Type', 'Visibility', 'DTO Attr', 'Flags'],
                $propertyRows,
            );
        }

        // Methods
        if (!empty($analysis['methods'])) {
            $this->line('');
            $this->info('🔧 Methods:');
            $methodRows = [];

            foreach ($analysis['methods'] as $method) {
                $methodRows[] = [
                    $method['name'],
                    $method['visibility'],
                    $method['is_static'] ? 'Static' : 'Instance',
                    $method['parameters'],
                    $method['return_type'] ?? 'mixed',
                ];
            }

            $this->table(
                ['Name', 'Visibility', 'Type', 'Params', 'Return Type'],
                $methodRows,
            );
        }
    }

    /**
     * Resolve class name with common namespaces.
     */
    private function resolveClassName(string $className): string
    {
        // If already fully qualified, return as is
        if (str_contains($className, '\\')) {
            return $className;
        }

        // Try common DTO namespaces
        $namespaces = [
            'App\\Data\\',
            'App\\DTOs\\',
            'App\\DTO\\',
            'App\\',
            '', // Global namespace
        ];

        foreach ($namespaces as $namespace) {
            $fullClassName = $namespace . $className;
            if (class_exists($fullClassName)) {
                return $fullClassName;
            }
        }

        return $className;
    }

    /**
     * Check if class is a DTO.
     */
    /**
     * @param ReflectionClass<object> $class
     */
    private function isDTOClass(ReflectionClass $class): bool
    {
        // Check if implements DTOInterface
        if ($class->implementsInterface(DTOInterface::class)) {
            return true;
        }

        // Check if uses DTOTrait
        $traits = $class->getTraitNames();
        foreach ($traits as $trait) {
            if (str_contains($trait, 'DTOTrait')) {
                return true;
            }
        }

        // Check if extends LaravelArcDTO
        $parent = $class->getParentClass();
        while ($parent) {
            if (str_contains($parent->getName(), 'LaravelArcDTO')) {
                return true;
            }
            $parent = $parent->getParentClass();
        }

        return false;
    }

    /**
     * Get property visibility.
     */
    private function getPropertyVisibility(ReflectionProperty $property): string
    {
        if ($property->isPrivate()) {
            return 'private';
        }
        if ($property->isProtected()) {
            return 'protected';
        }

        return 'public';
    }

    /**
     * Get method visibility.
     */
    private function getMethodVisibility(ReflectionMethod $method): string
    {
        if ($method->isPrivate()) {
            return 'private';
        }
        if ($method->isProtected()) {
            return 'protected';
        }

        return 'public';
    }
}
