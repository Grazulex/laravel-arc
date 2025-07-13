<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Headers\TraitsHeaderGenerator;

$generator = new TraitsHeaderGenerator();
$context = new DtoGenerationContext();

$header = ['traits' => ['HasUuid']];
$result = $generator->generate('traits', $header, $context);

echo "Generated traits:\n";
echo "'".$result."'\n";
