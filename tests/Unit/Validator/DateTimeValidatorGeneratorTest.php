<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\DateTimeValidatorGenerator;

describe('DateTimeValidatorGenerator', function () {
    it('supports datetime/date/time types', function () {
        $gen = new DateTimeValidatorGenerator();

        expect($gen->supports('datetime'))->toBeTrue();
        expect($gen->supports('date'))->toBeTrue();
        expect($gen->supports('time'))->toBeTrue();
        expect($gen->supports('string'))->toBeFalse();
    });

    it('generates date rule with required', function () {
        $generator = new DateTimeValidatorGenerator();

        $rules = $generator->generate('published_at', [
            'type' => 'datetime',
            'required' => true,
        ]);

        expect($rules)->toBe([
            'published_at' => ['date', 'required'],
        ]);
    });

    it('returns only date rule when no extras given', function () {
        $generator = new DateTimeValidatorGenerator();

        $rules = $generator->generate('published_at', ['type' => 'datetime']);

        expect($rules)->toBe([
            'published_at' => ['date'],
        ]);
    });
});
