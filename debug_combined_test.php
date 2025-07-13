<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Grazulex\LaravelArc\Generator\DtoGenerator;

$generator = DtoGenerator::make();

// Test the exact YAML from the failing test
$yaml = [
    'header' => [
        'dto' => 'CombinedTestDTO',
        'traits' => ['HasUuid', 'HasTimestamps', 'HasVersioning'],
    ],
    'fields' => [
        'name' => ['type' => 'string', 'required' => true],
    ],
];

echo "=== COMBINED TEST ===\n";
$result = $generator->generateFromDefinition($yaml);
echo $result;
echo "\n\n";

// Check specific parts
echo "Has 'public readonly \\Carbon\\Carbon \$created_at,': ";
echo str_contains($result, 'public readonly \Carbon\Carbon $created_at,') ? 'YES' : 'NO';
echo "\n";

echo "Has 'public readonly \\Carbon\\Carbon \$created_at = null,': ";
echo str_contains($result, 'public readonly \Carbon\Carbon $created_at = null,') ? 'YES' : 'NO';
echo "\n";
