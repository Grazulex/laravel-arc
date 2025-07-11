<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('can generate a DTO from the full-featured YAML fixture', function () {
    // 1. Dossier temporaire simulant config('dto.base_path')
    $definitionPath = __DIR__.'/temp_definitions';
    File::ensureDirectoryExists($definitionPath);
    config()->set('dto.base_path', $definitionPath);

    // 2. Copie de la fixture YAML dans ce dossier
    $yamlName = 'full-featured.yaml';
    $yamlSource = __DIR__.'/DtoGenerator/fixtures/full-featured.yaml';
    $yamlTarget = $definitionPath.'/'.$yamlName;
    File::copy($yamlSource, $yamlTarget);

    // 3. Cible du DTO généré (namespace App\DTOs => App/DTOs/)
    $dtoPath = app_path('App/DTOs/ProductDTO.php'); // ✅ Correction ici
    File::delete($dtoPath); // Nettoyage préalable

    // 4. Exécution de la commande
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
});

it('can preview a DTO class using --dry-run', function () {
    $definitionPath = __DIR__.'/temp_definitions';
    File::ensureDirectoryExists($definitionPath);
    config()->set('dto.base_path', $definitionPath);

    $yamlName = 'full-featured.yaml';
    $yamlSource = __DIR__.'/DtoGenerator/fixtures/full-featured.yaml';
    $yamlTarget = $definitionPath.'/'.$yamlName;
    File::copy($yamlSource, $yamlTarget);

    // Chemin vers le fichier qui NE DOIT PAS être créé
    $dtoPath = app_path('App/DTOs/ProductDTO.php');
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
});
