<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('can generate a DTO from the full-featured YAML fixture', function () {
    // Initialize traits registry
    BehavioralTraitRegistry::registerDefaults();

    // 1. Dossier temporaire simulant config('dto.definitions_path')
    $definitionPath = __DIR__.'/temp_definitions';
    File::ensureDirectoryExists($definitionPath);
    config()->set('dto.definitions_path', $definitionPath);

    // 2. Copie de la fixture YAML dans ce dossier
    $yamlName = 'full-featured.yaml';
    $yamlSource = __DIR__.'/DtoGenerator/fixtures/full-featured.yaml';
    $yamlTarget = $definitionPath.'/'.$yamlName;
    File::copy($yamlSource, $yamlTarget);

    // 3. Configurer le chemin de sortie pour les tests
    $outputPath = __DIR__.'/temp_output';
    config()->set('dto.output_path', $outputPath);
    config()->set('dto.namespace', 'App\\DTO'); // Set the expected namespace

    // 4. Cible du DTO généré - le fichier sera créé dans le chemin configuré
    $dtoPath = $outputPath.'/ProductDTO.php';
    File::delete($dtoPath); // Nettoyage préalable

    // 5. Exécution de la commande
    $exitCode = Artisan::call('dto:generate', [
        'filename' => $yamlName,
        '--force' => true,
    ]);

    // 6. Vérifications
    expect($exitCode)->toBe(0);
    expect(File::exists($dtoPath))->toBeTrue();
    expect(File::get($dtoPath))->toContain('class ProductDTO');

    // 7. Nettoyage
    File::deleteDirectory($definitionPath);
    File::deleteDirectory($outputPath);
});

it('can preview a DTO class using --dry-run', function () {
    $definitionPath = __DIR__.'/temp_definitions';
    File::ensureDirectoryExists($definitionPath);
    config()->set('dto.definitions_path', $definitionPath);

    // Configurer le chemin de sortie pour les tests
    $outputPath = __DIR__.'/temp_output';
    config()->set('dto.output_path', $outputPath);
    config()->set('dto.namespace', 'App\\DTO'); // Set the expected namespace

    $yamlName = 'full-featured.yaml';
    $yamlSource = __DIR__.'/DtoGenerator/fixtures/full-featured.yaml';
    $yamlTarget = $definitionPath.'/'.$yamlName;
    File::copy($yamlSource, $yamlTarget);

    // Chemin vers le fichier qui NE DOIT PAS être créé
    $dtoPath = $outputPath.'/ProductDTO.php';
    File::delete($dtoPath);

    // ➕ Test du mode dry-run
    $exitCode = Artisan::call('dto:generate', [
        'filename' => $yamlName,
        '--dry-run' => true,
    ]);

    $output = Artisan::output();

    expect($exitCode)->toBe(0);
    expect($output)->toContain('final class ProductDTO');
    expect($output)->toContain('namespace App\\DTO');
    expect($output)->toContain('public readonly string $name'); // champ existant dans YAML
    expect($output)->not->toContain('DTO class written to');
    expect(File::exists($dtoPath))->toBeFalse();

    File::deleteDirectory($definitionPath);
    File::deleteDirectory($outputPath);
});
