<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Fields\TimeFieldGenerator;

describe('TimeFieldGenerator', function () {
    it('supports time type', function () {
        $generator = new TimeFieldGenerator();

        expect($generator->supports('time'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates non-required time field with null default', function () {
        $generator = new TimeFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('alarm', [
            'required' => false,
        ], $context);

        expect($code)->toBe('public ?\\Carbon\\Carbon $alarm = null;');
    });
});
