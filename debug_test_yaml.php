<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Grazulex\LaravelArc\Generator\DtoGenerator;

$generator = DtoGenerator::make();

// Test the exact YAML from the failing test
$yaml = [
    'header' => [
        'dto' => 'VersionTestDTO',
        'traits' => ['HasVersioning'],
    ],
    'fields' => [
        'name' => ['type' => 'string', 'required' => true],
    ],
];

echo "=== TEST VERSIONING (from actual test) ===\n";
$result = $generator->generateFromDefinition($yaml);
echo $result;
echo "\n\n";

echo 'Generated class name: ';
if (preg_match('/final class (\w+)/', $result, $matches)) {
    echo $matches[1];
} else {
    echo 'NOT FOUND';
}
echo "\n";
