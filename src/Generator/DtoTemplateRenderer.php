<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Support\FieldTypeResolver;

final class DtoTemplateRenderer
{
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
        string $extendsClause = ''
    ): string {
        // Utiliser les propriétés pré-rendues au lieu de régénérer
        $constructor = collect($renderedProperties)->map(function (string $property, string $name): string {
            // Transformer les propriétés de classe en paramètres de constructeur
            $property = mb_trim($property);

            // Si c'est un champ DTO (déjà avec readonly et virgule), ne pas changer
            if (str_contains($property, 'readonly') && str_ends_with($property, ',')) {
                return str_repeat(' ', 8).$property;
            }

            // Sinon, transformer le format standard en format readonly avec virgule
            if (str_ends_with($property, ';')) {
                $property = mb_substr($property, 0, -1).',';
            }

            // Ajouter readonly si pas déjà présent
            if (! str_contains($property, 'readonly')) {
                $property = str_replace('public ', 'public readonly ', $property);
            }

            return str_repeat(' ', 8).$property;
        })->implode("\n");

        $baseMethods = [
            $this->renderFromModel($modelFQCN, $fieldDefinitions),
            $this->renderToArray($fieldDefinitions),
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
}
