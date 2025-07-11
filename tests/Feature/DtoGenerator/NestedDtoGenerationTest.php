<?php

declare(strict_types=1);

namespace Tests\Feature\DtoGenerator;

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\DtoGenerator;
use Symfony\Component\Yaml\Yaml;

it('can generate DTO with nested DTOs', function () {
    $yaml = Yaml::parseFile(__DIR__.'/fixtures/nested-dto.yaml');
    $context = new DtoGenerationContext();

    $generator = DtoGenerator::make();
    $code = $generator->generateFromDefinition($yaml);

    // Vérifier que le DTO principal est généré
    expect($code)->toContain('final class PostDTO');
    expect($code)->toContain('namespace App\\DTO');

    // Vérifier que les champs DTO imbriqués sont générés correctement
    expect($code)->toContain('public readonly \\UserDTO $author');
    expect($code)->toContain('public readonly ?\\CategoryDTO $category');

    // Vérifier que les champs normaux sont toujours présents
    expect($code)->toContain('public readonly string $id');
    expect($code)->toContain('public readonly string $title');
    expect($code)->toContain('public readonly string $content');

    // Vérifier que les règles de validation sont générées
    expect($code)->toContain('public static function rules(): array');
    expect($code)->toContain("'author' => ['array', 'required']");
    expect($code)->toContain("'category' => ['array']");
});

it('handles circular references safely', function () {
    $yaml = Yaml::parseFile(__DIR__.'/fixtures/circular-dto.yaml');
    $context = new DtoGenerationContext(2); // Limite de profondeur à 2

    $generator = DtoGenerator::make();
    $code = $generator->generateFromDefinition($yaml);

    // Vérifier que le DTO principal est généré
    expect($code)->toContain('final class CircularTestDTO');

    // Le champ parent devrait être généré normalement au premier niveau
    expect($code)->toContain('public readonly ?\\CircularTestDTO $parent');

    // Les règles de validation devraient traiter les DTOs comme des arrays
    expect($code)->toContain("'parent' => ['array']");
    expect($code)->toContain("'related' => ['array']");
});

it('prevents infinite nesting beyond max depth', function () {
    $context = new DtoGenerationContext(1); // Limite très basse

    // Simuler une situation où on est déjà au maximum
    $context->enterDto('TestDTO');

    expect($context->canNestDto('AnotherDTO'))->toBeFalse();
    expect($context->getCurrentDepth())->toBe(1);
});

it('tracks DTO nesting context correctly', function () {
    $context = new DtoGenerationContext(3);

    expect($context->getCurrentDepth())->toBe(0);
    expect($context->canNestDto('UserDTO'))->toBeTrue();

    $context->enterDto('UserDTO');
    expect($context->getCurrentDepth())->toBe(1);
    expect($context->canNestDto('UserDTO'))->toBeFalse(); // Circular reference
    expect($context->canNestDto('PostDTO'))->toBeTrue();  // Different DTO

    $context->enterDto('PostDTO');
    expect($context->getCurrentDepth())->toBe(2);

    $context->exitDto();
    expect($context->getCurrentDepth())->toBe(1);

    $context->exitDto();
    expect($context->getCurrentDepth())->toBe(0);
});
