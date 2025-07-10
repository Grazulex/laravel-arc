<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Options\SoftDeletesOptionGenerator;

describe('SoftDeletesOptionGenerator', function () {
    it('returns code when enabled', function () {
        $generator = new SoftDeletesOptionGenerator();
        $result = $generator->expandFields(true);

        expect($result)->toBe([
            'deleted_at' => ['type' => 'datetime', 'required' => false],
        ]);
    });

    it('returns null when disabled', function () {
        $generator = new SoftDeletesOptionGenerator();
        $result = $generator->expandFields(false);

        expect($result)->toBe([]);
    });
});
