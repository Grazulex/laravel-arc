<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Fields\DateTimeFieldGenerator;

describe('DateTimeFieldGenerator', function () {
    it('supports datetime type', function () {
        $generator = new DateTimeFieldGenerator();

        expect($generator->supports('datetime'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates non-required datetime field with null default', function () {
        $generator = new DateTimeFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('published_at', [
            'required' => false,
        ], $context);

        expect($code)->toBe('public ?\\Carbon\\Carbon $published_at = null;');
    });

    it('generates required datetime field without default', function () {
        $generator = new DateTimeFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('updated_at', [
            'required' => true,
        ], $context);

        expect($code)->toBe('public \\Carbon\\Carbon $updated_at;');
    });

    it('ignores string default value for datetime', function () {
        $generator = new DateTimeFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('scheduled_at', [
            'default' => '2024-07-09 13:00:00',
            'required' => true,
        ], $context);

        expect($code)->toBe('public \\Carbon\\Carbon $scheduled_at;');
    });

    it('handles explicit null default for datetime', function () {
        $generator = new DateTimeFieldGenerator();
        $context = new DtoGenerationContext();

        $code = $generator->generate('deleted_at', [
            'default' => null,
            'required' => false,
        ], $context);

        expect($code)->toBe('public ?\\Carbon\\Carbon $deleted_at = null;');
    });
});
