<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Grazulex\LaravelArc\Generator\DtoGenerator;

$yaml = [
    'header' => [
        'dto' => 'UuidTestDTO',
        'traits' => ['HasUuid'],
    ],
    'fields' => [
        'name' => ['type' => 'string', 'required' => true],
    ],
];

$generator = DtoGenerator::make();
$result = $generator->generateFromDefinition($yaml);

echo "Generated DTO:\n";
echo $result."\n";
