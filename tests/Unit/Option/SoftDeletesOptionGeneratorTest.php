<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Options\SoftDeletesOptionGenerator;

describe('SoftDeletesOptionGenerator', function () {
    it('returns code when enabled', function () {
        $generator = new SoftDeletesOptionGenerator();
        $result = $generator->generate(true);

        expect($result)->toBe('public bool $softDeletes = true;');
    });

    it('returns null when disabled', function () {
        $generator = new SoftDeletesOptionGenerator();
        $result = $generator->generate(false);

        expect($result)->toBeNull();
    });
});
