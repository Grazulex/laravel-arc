<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\DateTimeValidatorGenerator;

describe('DateTimeValidatorGenerator', function () {
    it('supports datetime-like types', function () {
        $generator = new DateTimeValidatorGenerator();

        expect($generator->supports('datetime'))->toBeTrue();
        expect($generator->supports('date'))->toBeTrue();
        expect($generator->supports('time'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates datetime rule with required and custom rules', function () {
        $generator = new DateTimeValidatorGenerator();

        $rules = $generator->generate('published_at', [
            'type' => 'datetime',
            'required' => true,
            'rules' => ['date_format:Y-m-d H:i:s'],
        ]);

        expect($rules)->toBe([
            'published_at' => ['datetime', 'required', 'date_format:Y-m-d H:i:s'],
        ]);
    });

    it('returns rule without required if not specified', function () {
        $generator = new DateTimeValidatorGenerator();

        $rules = $generator->generate('created_at', [
            'type' => 'date',
        ]);

        expect($rules)->toBe([
            'created_at' => ['date', 'required'],
        ]);
    });

    it('returns empty array for unsupported type', function () {
        $generator = new DateTimeValidatorGenerator();

        $rules = $generator->generate('custom_field', [
            'type' => 'array',
        ]);

        expect($rules)->toBe([]);
    });
});
