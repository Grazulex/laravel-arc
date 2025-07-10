<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerator;
use Symfony\Component\Yaml\Yaml;

describe('DtoGenerator - full YAML coverage', function () {
    it('can generate a complete DTO class from a realistic YAML definition', function () {
        $yaml = file_get_contents(__DIR__.'/fixtures/basic-complete.yaml');
        $definition = Yaml::parse($yaml);

        $code = DtoGenerator::make()->generateFromDefinition($definition);

        expect($code)
            ->toContain('final class ProductDTO')
            ->toContain('public readonly ?float $price')
            ->toContain('public static function fromModel')
            ->toContain("'status' => ['in:draft,published,archived', 'required']")
            ->toContain('public static function validate')
            ->toContain("'price' => \$this->price");
    });

    it('can generate a complete DTO class from a full-featured YAML definition', function () {
        $yaml = file_get_contents(__DIR__.'/fixtures/full-featured.yaml');
        $definition = Yaml::parse($yaml);

        $code = DtoGenerator::make()->generateFromDefinition($definition);

        expect($code)
            ->toContain('final class ProductDTO')
            ->toContain('public readonly string $name')
            ->toContain('public readonly ?string $description')
            ->toContain('public readonly float $price')
            ->toContain('public readonly ?array $tags')
            ->toContain('public readonly bool $available')
            ->toContain('public static function fromModel')
            ->toContain('public function toArray')
            ->toContain('public static function rules')
            ->toContain("'status' => ['in:draft,published,archived', 'required']")
            ->toContain("'price' => ['numeric', 'required', 'min:0']")
            ->toContain("'tags' => ['array', 'required', 'distinct']")
            ->toContain("'description' => ['string', 'required', 'max:500']")
            ->toContain('public static function validate')
            ->toContain('category') // relation
            ->toContain('public readonly \\Carbon\\Carbon $created_at') // timestamps
            ->toContain('public readonly ?\\Carbon\\Carbon $updated_at') // timestamps
            ->toContain('public readonly ?\\Carbon\\Carbon $deleted_at'); // soft deletes
    });
});
