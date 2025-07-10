<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Headers\ModelHeaderGenerator;

describe('ModelHeaderGenerator', function () {
    it('generates use statement from model header', function () {
        $generator = new ModelHeaderGenerator();

        $yaml = ['model' => 'App\Models\Post'];
        $result = $generator->generate('model', $yaml, new DtoGenerationContext());

        expect($result)->toBe('\\App\Models\Post');
    });

    it('trims backslashes from model header', function () {
        $generator = new ModelHeaderGenerator();

        $yaml = ['model' => '\\App\\Models\\Post'];
        $result = $generator->generate('model', $yaml, new DtoGenerationContext());

        expect($result)->toBe('\\App\Models\\Post');
    });

    it('returns default value if model header is missing', function () {
        $generator = new ModelHeaderGenerator();

        $yaml = [];
        $result = $generator->generate('model', $yaml, new DtoGenerationContext());

        expect($result)->toBe('\\App\\Models\\Model');
    });

    it('returns default value if model header is not a string', function () {
        $generator = new ModelHeaderGenerator();

        $yaml = ['model' => ['not' => 'a string']];
        $result = $generator->generate('model', $yaml, new DtoGenerationContext());

        // Le cast échoue, donc le fallback s'applique
        expect($result)->toBe('\\App\\Models\\Model');
    });
});
