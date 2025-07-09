<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\DateTimeValidatorGenerator;

describe('DateTimeValidatorGenerator', function () {
    it('supports datetime-like types', function () {
        $gen = new DateTimeValidatorGenerator();

        expect($gen->supports('datetime'))->toBeTrue();
        expect($gen->supports('date'))->toBeTrue();
        expect($gen->supports('time'))->toBeTrue();
        expect($gen->supports('string'))->toBeFalse();
    });

    it('generates datetime rule with required and custom format', function () {
        $gen = new DateTimeValidatorGenerator();

        $rules = $gen->generate('starts_at', [
            'type' => 'datetime',
            'rules' => ['date_format:Y-m-d H:i:s'],
        ]);

        expect($rules)->toBe([
            'starts_at' => ['required', 'datetime', 'date_format:Y-m-d H:i:s'],
        ]);
    });

    it('generates date rule without required if nullable is true', function () {
        $gen = new DateTimeValidatorGenerator();

        $rules = $gen->generate('published_on', [
            'type' => 'date',
            'nullable' => true,
        ]);

        expect($rules)->toBe([
            'published_on' => ['date'],
        ]);
    });

    it('returns empty array if type does not match', function () {
        $gen = new DateTimeValidatorGenerator();

        $rules = $gen->generate('updated_at', ['type' => 'array']);

        expect($rules)->toBe([]);
    });
});
