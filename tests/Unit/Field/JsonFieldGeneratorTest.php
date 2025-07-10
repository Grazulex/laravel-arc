<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Fields\JsonFieldGenerator;

describe('JsonFieldGenerator', function () {
    it('supports json type', function () {
        $generator = new JsonFieldGenerator();

        expect($generator->supports('json'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates non-required json field with null default', function () {
        $generator = new JsonFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('metadata', [
            'required' => false,
        ], $context);

        expect($code)->toBe('public ?array $metadata = null;');
    });

    it('generates json field with default value', function () {
        $generator = new JsonFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('metadata', [
            'default' => ['key' => 'value'],
        ], $context);

        expect($code)->toContain('public');
        expect($code)->toContain('$metadata =');
    });
});
