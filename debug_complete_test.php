<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Grazulex\LaravelArc\Generator\DtoGenerator;
use Symfony\Component\Yaml\Yaml;

echo "=== Debugging CompleteDtoGenerationTest ===\n\n";

// Test the full-featured YAML
$yaml = file_get_contents(__DIR__.'/tests/Feature/DtoGenerator/fixtures/full-featured.yaml');
$definition = Yaml::parse($yaml);

echo "YAML definition:\n";
print_r($definition);
echo "\n";

$code = DtoGenerator::make()->generateFromDefinition($definition);

echo "Generated code:\n";
echo $code;
echo "\n\n";

// Search for timestamps
echo "=== Searching for timestamp patterns ===\n";
$lines = explode("\n", $code);
foreach ($lines as $i => $line) {
    if (mb_stripos($line, 'created_at') !== false || mb_stripos($line, 'updated_at') !== false || mb_stripos($line, 'deleted_at') !== false) {
        echo 'Line '.($i + 1).': '.mb_trim($line)."\n";
    }
}
