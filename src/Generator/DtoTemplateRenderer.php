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
        string $methods
    ): string {
        $stub = file_get_contents(__DIR__.'/../Console/Commands/stubs/dto.class.stub');

        return str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ properties }}', '{{ constructor }}', '{{ methods }}'],
            [$namespace, $className, $properties, $constructor, $methods],
            $stub
        );
    }

    public function renderFullDto(
        string $namespace,
        string $className,
        array $fields,
        string $modelFQCN,
        array $extraMethods = []
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
            implode("\n\n", array_merge($baseMethods, $extraMethods))
        );
    }
}
