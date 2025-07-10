<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Options\TimestampsOptionGenerator;

describe('TimestampsOptionGenerator', function () {
    it('returns code when enabled', function () {
        $generator = new TimestampsOptionGenerator();
        $result = $generator->expandFields(true);

        expect($result)->toBe([
            'created_at' => ['type' => 'datetime'],
            'updated_at' => ['type' => 'datetime', 'nullable' => true],
        ]);
    });

    it('returns null when disabled', function () {
        $generator = new TimestampsOptionGenerator();
        $result = $generator->expandFields(false);

        expect($result)->toBe([]);
    });
});
