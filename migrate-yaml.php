#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Script pour migrer automatiquement les YAML avec options vers le nouveau format trait-based
 */
$files = [
    'examples/product.yaml',
    'examples/user.yaml',
    'examples/circular-category.yaml',
    'examples/user-dto-example.yaml',
    'examples/enum-examples.yaml',
    'examples/profile.yaml',
    'examples/nested-order.yaml',
    'examples/nested-customer.yaml',
    'examples/nested-address.yaml',
    'examples/nested-country.yaml',
    'examples/advanced-user.yaml',
    'examples/advanced-options.yaml',
];

// Mapping des options vers les traits
$optionToTraitMapping = [
    'timestamps' => 'HasTimestamps',
    'soft_deletes' => 'HasSoftDeletes',
    'uuid' => 'HasUuid',
    'versioning' => 'HasVersioning',
    'taggable' => 'HasTagging',
    'auditable' => 'HasAuditing',
    'cacheable' => 'HasCaching',
];

foreach ($files as $file) {
    if (! file_exists($file)) {
        echo "Skipping $file (not found)\n";

        continue;
    }

    $content = file_get_contents($file);
    $yaml = yaml_parse($content);

    if (! $yaml) {
        echo "Error parsing $file\n";

        continue;
    }

    $modified = false;

    // Migrer la structure
    if (isset($yaml['options'])) {
        $options = $yaml['options'];
        $traits = [];

        // Convertir les options en traits
        foreach ($options as $option => $value) {
            if (isset($optionToTraitMapping[$option]) && $value) {
                $traits[] = $optionToTraitMapping[$option];
            }
        }

        // Restructurer le YAML
        if (isset($yaml['header'])) {
            // Ajouter les traits dans le header existant
            if (! empty($traits)) {
                $yaml['header']['traits'] = $traits;
            }
        } else {
            // Créer le nouveau format sans header
            $newYaml = [];
            $newYaml['namespace'] = $options['namespace'] ?? 'App\\DTO';
            $newYaml['class_name'] = $yaml['class'] ?? 'GeneratedDto';

            if (isset($yaml['model'])) {
                $newYaml['model'] = $yaml['model'];
            }

            if (! empty($traits)) {
                $newYaml['traits'] = $traits;
            }

            if (isset($yaml['fields'])) {
                $newYaml['fields'] = $yaml['fields'];
            }

            if (isset($yaml['relations'])) {
                $newYaml['relations'] = $yaml['relations'];
            }

            $yaml = $newYaml;
        }

        // Supprimer la section options
        unset($yaml['options']);
        $modified = true;
    }

    if ($modified) {
        // Créer la version migrée
        $newFile = str_replace('.yaml', '-migrated.yaml', $file);
        file_put_contents($newFile, yaml_emit($yaml, YAML_UTF8_ENCODING));
        echo "Migrated $file -> $newFile\n";
    } else {
        echo "No migration needed for $file\n";
    }
}

echo "Migration complete!\n";
