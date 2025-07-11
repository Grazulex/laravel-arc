<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Grazulex\LaravelArc\Generator\DtoGenerator;

$yaml = [
    'header' => [
        'dto' => 'TestDTO',
        'model' => 'App\Models\Test',
        'table' => 'tests',
    ],
    'fields' => [
        'id' => [
            'type' => 'uuid',
            'required' => true,
        ],
        'name' => [
            'type' => 'string',
            'required' => true,
        ],
        // Enum traditionnel
        'status' => [
            'type' => 'enum',
            'values' => ['active', 'inactive'],
            'required' => true,
            'default' => 'active',
        ],
        // Enum PHP 8.0
        'priority' => [
            'type' => 'enum',
            'class' => 'Tests\Fixtures\Enums\Priority',
            'required' => false,
        ],
        // Enum PHP 8.0 avec valeur par défaut
        'category' => [
            'type' => 'enum',
            'class' => 'Tests\Fixtures\Enums\Status',
            'required' => true,
            'default' => 'draft',
        ],
    ],
    'options' => [
        'namespace' => 'App\DTO',
    ],
];

$generator = DtoGenerator::make();
$code = $generator->generateFromDefinition($yaml);
echo $code;
