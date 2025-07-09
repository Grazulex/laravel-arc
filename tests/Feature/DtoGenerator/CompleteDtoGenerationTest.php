<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerator;
use Symfony\Component\Yaml\Yaml;

it('can generate a complete DTO class from a realistic YAML definition', function () {
    $yaml = file_get_contents(__DIR__.'/fixtures/basic-complete.yaml');
    $definition = Yaml::parse($yaml);

    $code = DtoGenerator::make()->generateFromDefinition($definition);

    expect($code)
        ->toContain('final class ProductData')
        ->toContain('public readonly ?float $price')
        ->toContain('public static function fromModel')
        ->toContain("'status' => ['in:draft,published,archived', 'required']")
        ->toContain('public static function validate')
        ->toContain("'price' => \$this->price");
});
