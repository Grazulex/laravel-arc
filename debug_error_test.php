<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\DtoGenerator;

// Initialize the generator
$context = new DtoGenerationContext();
$generator = new DtoGenerator($context);

// Test the complex field configuration (updating to use traits)
$yamlContent = [
    'header' => [
        'dto' => 'ComplexDto',
        'namespace' => 'App\DTOs',
        'traits' => ['HasTimestamps', 'HasSoftDeletes'],
    ],
    'fields' => [
        'id' => [
            'type' => 'integer',
            'required' => true,
        ],
        'name' => [
            'type' => 'string',
            'required' => true,
            'max' => 255,
        ],
        'email' => [
            'type' => 'string',
            'required' => true,
            'email' => true,
        ],
        'created_at' => [
            'type' => 'datetime',
            'required' => false,
        ],
    ],
];

echo "=== Generating ComplexDto ===\n";
try {
    $result = $generator->generateFromDefinition($yamlContent, 'test.yaml');
    echo $result;
    echo "\n\n=== Generated Successfully ===\n";
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
    echo 'Trace: '.$e->getTraceAsString()."\n";
}
