<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Support\FieldTypeResolver;

final class DtoTemplateRenderer
{
    public function render(
        string $namespace,
        string $className,
        string $properties,
        string $constructor,
        string $methods,
        string $headerExtra = '',
        string $extendsClause = '',
        string $behavioralTraits = ''
    ): string {
        $stub = file_get_contents(__DIR__.'/../Console/Commands/stubs/dto.class.stub');

        return str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ properties }}', '{{ constructor }}', '{{ methods }}', '{{ header_extra }}', '{{ extends_clause }}', '{{ behavioral_traits }}'],
            [$namespace, $className, $properties, $constructor, $methods, $headerExtra, $extendsClause, $behavioralTraits],
            $stub
        );
    }

    public function renderFromModel(string $modelFQCN, array $fields): string
    {
        $assignments = collect($fields)
            ->map(fn ($_def, string $field): string => str_repeat(' ', 12)."{$field}: \$model->{$field},")
            ->implode("\n");

        $stub = file_get_contents(__DIR__.'/../Console/Commands/stubs/dto.from-model.stub');

        return str_replace(
            ['{{ modelFQCN }}', '{{ assignments }}'],
            [$modelFQCN, $assignments],
            $stub
        );
    }

    public function renderToArray(array $fields): string
    {
        $exports = collect($fields)
            ->map(fn ($_def, string $field): string => str_repeat(' ', 12)."'{$field}' => \$this->{$field},")
            ->implode("\n");

        $stub = file_get_contents(__DIR__.'/../Console/Commands/stubs/dto.to-array.stub');

        return str_replace('{{ exports }}', $exports, $stub);
    }

    public function renderFromArray(array $fields): string
    {
        $assignments = [];
        $hasTransformers = false;

        foreach ($fields as $field => $config) {
            if (isset($config['transformers']) && is_array($config['transformers']) && (isset($config['transformers']) && $config['transformers'] !== [])) {
                $hasTransformers = true;
                $transformerList = "'".implode("', '", $config['transformers'])."'";
                $assignments[] = str_repeat(' ', 12)."{$field}: \$registry->transform(\$data['{$field}'] ?? null, [{$transformerList}]),";
            } else {
                $assignments[] = str_repeat(' ', 12)."{$field}: \$data['{$field}'] ?? null,";
            }
        }

        $registryInit = $hasTransformers ? "\n        \$registry = new \\Grazulex\\LaravelArc\\Support\\Transformers\\FieldTransformerRegistry();\n" : '';

        $stub = file_get_contents(__DIR__.'/../Console/Commands/stubs/dto.from-array.stub');

        return str_replace(
            ['{{ registry_init }}', '{{ assignments }}'],
            [$registryInit, implode("\n", $assignments)],
            $stub
        );
    }

    public function renderClass(
        string $namespace,
        string $className,
        string $properties,
        string $constructor,
        string $methods,
        string $headerExtra = '',
        string $extendsClause = ''
    ): string {
        $stub = file_get_contents(__DIR__.'/../Console/Commands/stubs/dto.class.stub');

        return str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ properties }}', '{{ constructor }}', '{{ methods }}', '{{ header_extra }}', '{{ extends_clause }}'],
            [$namespace, $className, $properties, $constructor, $methods, $headerExtra, $extendsClause],
            $stub
        );
    }

    public function renderFullDto(
        string $namespace,
        string $className,
        array $fields,
        string $modelFQCN,
        array $extraMethods = [],
        string $headerExtra = '',
        string $extendsClause = ''
    ): string {
        $constructor = collect($fields)->map(
            function (array $definition, string $name): string {
                $type = FieldTypeResolver::resolvePhpType(
                    $definition['type'] ?? 'mixed',
                    $definition['required'] ?? true
                );

                $default = array_key_exists('default', $definition) && $definition['default'] === null
                    ? ' = null'
                    : '';

                return str_repeat(' ', 8)."public readonly {$type} \${$name}{$default},";
            }
        )->implode("\n");

        $baseMethods = [
            $this->renderFromModel($modelFQCN, $fields),
            $this->renderToArray($fields),
        ];

        return $this->renderClass(
            $namespace,
            $className,
            '', // properties block not used
            $constructor,
            implode("\n\n", array_merge($baseMethods, $extraMethods)),
            $headerExtra,
            $extendsClause
        );
    }

    public function renderFullDtoWithRenderedProperties(
        string $namespace,
        string $className,
        array $renderedProperties,
        array $fieldDefinitions,
        string $modelFQCN,
        array $extraMethods = [],
        string $headerExtra = '',
        string $extendsClause = '',
        string $behavioralTraits = ''
    ): string {
        // Use pre-rendered properties instead of regenerating
        $constructor = collect($renderedProperties)->map(function (string $property, string $name): string {
            // Transform class properties to constructor parameters
            $property = mb_trim($property);

            // If it's already a DTO field (with readonly and comma), don't change
            if (str_contains($property, 'readonly') && str_ends_with($property, ',')) {
                return str_repeat(' ', 8).$property;
            }

            // Otherwise, transform standard format to readonly format with comma
            if (str_ends_with($property, ';')) {
                $property = mb_substr($property, 0, -1).',';
            }

            // Add readonly if not already present
            if (! str_contains($property, 'readonly')) {
                $property = str_replace('public ', 'public readonly ', $property);
            }

            return str_repeat(' ', 8).$property;
        })->implode("\n");

        $baseMethods = [
            $this->renderFromModel($modelFQCN, $fieldDefinitions),
            $this->renderFromArray($fieldDefinitions),
            $this->renderToArray($fieldDefinitions),
        ];

        return $this->render(
            $namespace,
            $className,
            '', // properties block not used
            $constructor,
            implode("\n\n", array_merge($baseMethods, $extraMethods)),
            $headerExtra,
            $extendsClause,
            $behavioralTraits
        );
    }
}
