<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoTemplateRenderer;

describe('DtoTemplateRenderer', function () {
    it('can render a full DTO class from definition', function () {
        $renderer = new DtoTemplateRenderer();

        $namespace = 'App\\DTO';
        $className = 'UserData';
        $modelFQCN = '\\App\\Models\\User';

        $fields = [
            'id' => ['type' => 'integer', 'required' => true],
            'name' => ['type' => 'string', 'required' => true],
            'email' => ['type' => 'string', 'required' => false],
        ];

        $code = $renderer->renderFullDto($namespace, $className, $fields, $modelFQCN);

        expect($code)
            ->toContain('final class UserData')
            ->toContain('public readonly int $id')
            ->toContain('public readonly string $name')
            ->toContain('public readonly ?string $email')
            ->toContain('public static function fromModel')
            ->toContain('public function toArray')
            ->toContain("'email' => \$this->email");
    });

    it('can render only fromModel method', function () {
        $renderer = new DtoTemplateRenderer();

        $fields = [
            'id' => ['type' => 'integer'],
            'title' => ['type' => 'string'],
        ];

        $code = $renderer->renderFromModel('\\App\\Models\\Post', $fields);

        expect($code)
            ->toContain('public static function fromModel')
            ->toContain('id: $model->id')
            ->toContain('title: $model->title');
    });

    it('can render only toArray method', function () {
        $renderer = new DtoTemplateRenderer();

        $fields = [
            'slug' => ['type' => 'string'],
            'active' => ['type' => 'boolean'],
        ];

        $code = $renderer->renderToArray($fields);

        expect($code)
            ->toContain('public function toArray')
            ->toContain("'slug' => \$this->slug")
            ->toContain("'active' => \$this->active");
    });

    it('can handle required and optional types in renderFullDto', function () {
        $renderer = new DtoTemplateRenderer();

        $namespace = 'App\\DTO';
        $className = 'SettingsData';
        $modelFQCN = '\\App\\Models\\Settings';

        $fields = [
            'options' => ['type' => 'array', 'required' => true],
            'notes' => ['type' => 'string', 'required' => false],
        ];

        $code = $renderer->renderFullDto($namespace, $className, $fields, $modelFQCN);

        expect($code)
            ->toContain('public readonly array $options')
            ->toContain('public readonly ?string $notes')
            ->toContain("'options' => \$this->options")
            ->toContain("'notes' => \$this->notes");
    });

    it('can render datetime fields using Carbon type', function () {
        $renderer = new DtoTemplateRenderer();

        $namespace = 'App\\DTO';
        $className = 'EventData';
        $modelFQCN = '\\App\\Models\\Event';

        $fields = [
            'starts_at' => ['type' => 'datetime'],
            'ends_at' => ['type' => 'datetime', 'required' => false],
        ];

        $code = $renderer->renderFullDto($namespace, $className, $fields, $modelFQCN);

        expect($code)
            ->toContain('public readonly \\Carbon\\Carbon $starts_at')
            ->toContain('public readonly ?\\Carbon\\Carbon $ends_at')
            ->toContain("'starts_at' => \$this->starts_at")
            ->toContain("'ends_at' => \$this->ends_at");
    });
});
