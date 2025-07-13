<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoTemplateRenderer;
use Grazulex\LaravelArc\Generator\ModernDtoGenerator;
use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;

// Test du générateur moderne avec stubs
$registry = new BehavioralTraitRegistry();
$renderer = new DtoTemplateRenderer();
$generator = new ModernDtoGenerator($registry, $renderer);

// Charger le YAML moderne (nouveau format)
$yamlContent = file_get_contents(__DIR__.'/user-modern-traits-only-corrected.yaml');
$yamlData = yaml_parse($yamlContent);

// Générer le DTO
$generatedDto = $generator->generateFromDefinition($yamlData);

// Sauvegarder le résultat
file_put_contents(__DIR__.'/generated-user-modern-example.php', $generatedDto);

echo "DTO généré avec le système de stubs !\n";
echo "Fichier sauvé: generated-user-modern-example.php\n";
