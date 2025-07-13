<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Grazulex\LaravelArc\Generator\DtoGenerator;
use Symfony\Component\Yaml\Yaml;

$generator = DtoGenerator::make();

// Test versioning
$versioningYaml = <<<YAML
namespace: App\DTO
class: VersionTestDTO
model_fqcn: App\Models\VersionedModel
traits:
  - HasVersioning
fields:
  name:
    type: string
    required: true
YAML;

echo "=== VERSIONING TEST ===\n";
echo $generator->generateFromDefinition(Yaml::parse($versioningYaml));
echo "\n\n";

// Test tagging
$taggingYaml = <<<YAML
namespace: App\DTO
class: TagTestDTO
model_fqcn: App\Models\TaggableModel
traits:
  - HasTagging
fields:
  name:
    type: string
    required: true
YAML;

echo "=== TAGGING TEST ===\n";
echo $generator->generateFromDefinition(Yaml::parse($taggingYaml));
echo "\n\n";

// Test combined
$combinedYaml = <<<YAML
namespace: App\DTO
class: CombinedTestDTO
model_fqcn: App\Models\CombinedModel
traits:
  - HasUuid
  - HasTimestamps
  - HasVersioning
fields:
  name:
    type: string
    required: true
YAML;

echo "=== COMBINED TEST ===\n";
echo $generator->generateFromDefinition(Yaml::parse($combinedYaml));
