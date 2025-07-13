<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Options\VersioningOptionGenerator;

describe('VersioningOptionGenerator', function () {
    it('generates version field when enabled', function () {
        $generator = new VersioningOptionGenerator();
        $result = $generator->expandFields(true);

        expect($result)->toBe([
            'version' => [
                'type' => 'integer',
                'required' => true,
                'default' => 1,
                'rules' => ['integer', 'min:1'],
            ],
        ]);
    });

    it('returns empty array when disabled', function () {
        $generator = new VersioningOptionGenerator();
        $result = $generator->expandFields(false);

        expect($result)->toBe([]);
    });

    it('generates versioning methods when enabled', function () {
        $generator = new VersioningOptionGenerator();
        $context = new Grazulex\LaravelArc\Generator\DtoGenerationContext();

        $result = $generator->generate('versioning', true, $context);

        expect($result)
            ->toContain('nextVersion()')
            ->toContain('isNewerThan(')
            ->toContain('getVersionInfo()');
    });
});
