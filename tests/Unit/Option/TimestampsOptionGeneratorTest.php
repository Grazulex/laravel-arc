<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Options\TimestampsOptionGenerator;

describe('TimestampsOptionGenerator', function () {
    it('returns code when enabled', function () {
        $generator = new TimestampsOptionGenerator();
        $result = $generator->generate(true);

        expect($result)->toBe('public bool $timestamps = true;');
    });

    it('returns null when disabled', function () {
        $generator = new TimestampsOptionGenerator();
        $result = $generator->generate(false);

        expect($result)->toBeNull();
    });
});
