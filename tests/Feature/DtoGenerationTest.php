<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerator;
use Symfony\Component\Yaml\Yaml;

it('can generate full DTO class from YAML definition', function () {
    $yaml = <<<'YML'
header:
  dto: ProductDTO
  table: products
  model: App\Models\Product

fields:
  id:
    type: integer
  name:
    type: string
  price:
    type: float
    required: false
  created_at:
    type: datetime
YML;

    $definition = Yaml::parse($yaml);

    $generator = DtoGenerator::make();
    $code = $generator->generateFromDefinition($definition);

    expect($code)
        ->toContain('namespace App\\DTO')
        ->toContain('final class ProductDTO')
        ->toContain('public readonly int $id')
        ->toContain('public readonly string $name')
        ->toContain('public readonly ?float $price')
        ->toContain('public readonly \\Carbon\\Carbon $created_at')
        ->toContain('public static function fromModel')
        ->toContain('public function toArray');
});
