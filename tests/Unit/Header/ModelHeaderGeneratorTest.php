<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Headers\ModelHeaderGenerator;

describe('ModelHeaderGenerator', function () {
    it('generates use statement from model header', function () {
        $generator = new ModelHeaderGenerator();

        $yaml = ['model' => 'App\Models\Post'];
        $result = $generator->generate($yaml, 'PostDTO');

        expect($result)->toBe('use App\Models\Post;');
    });

    it('trims backslashes from model header', function () {
        $generator = new ModelHeaderGenerator();

        $yaml = ['model' => '\\App\\Models\\Post\\'];
        $result = $generator->generate($yaml, 'PostDTO');

        expect($result)->toBe('use App\Models\Post;');
    });

    it('returns null if model header is missing', function () {
        $generator = new ModelHeaderGenerator();

        $yaml = [];
        $result = $generator->generate($yaml, 'PostDTO');

        expect($result)->toBeNull();
    });

    it('returns null if model header is not a string', function () {
        $generator = new ModelHeaderGenerator();

        $yaml = ['model' => ['not' => 'a string']];
        $result = $generator->generate($yaml, 'PostDTO');

        expect($result)->toBeNull();
    });
});
