<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Options\UuidOptionGenerator;

describe('UuidOptionGenerator', function () {
    it('generates UUID field when enabled', function () {
        $generator = new UuidOptionGenerator();
        $result = $generator->expandFields(true);

        expect($result)->toBe([
            'id' => [
                'type' => 'uuid',
                'required' => true,
                'rules' => ['uuid'],
            ],
        ]);
    });

    it('returns empty array when disabled', function () {
        $generator = new UuidOptionGenerator();
        $result = $generator->expandFields(false);

        expect($result)->toBe([]);
    });

    it('generates UUID helper methods when enabled', function () {
        $generator = new UuidOptionGenerator();
        $context = new Grazulex\LaravelArc\Generator\DtoGenerationContext();

        $result = $generator->generate('uuid', true, $context);

        expect($result)
            ->toContain('generateUuid()')
            ->toContain('withGeneratedUuid(')
            ->toContain('\\Illuminate\\Support\\Str::uuid()');
    });

    it('returns empty string when disabled', function () {
        $generator = new UuidOptionGenerator();
        $context = new Grazulex\LaravelArc\Generator\DtoGenerationContext();

        $result = $generator->generate('uuid', false, $context);

        expect($result)->toBe('');
    });
});
