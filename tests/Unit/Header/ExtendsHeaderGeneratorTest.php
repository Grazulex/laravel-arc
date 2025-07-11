<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Headers\ExtendsHeaderGenerator;

describe('ExtendsHeaderGenerator', function () {
    it('supports extends key', function () {
        $generator = new ExtendsHeaderGenerator();

        expect($generator->supports('extends'))->toBeTrue();
        expect($generator->supports('use'))->toBeFalse();
    });

    it('generates extends clause', function () {
        $generator = new ExtendsHeaderGenerator();
        $context = new DtoGenerationContext();

        $header = [
            'extends' => 'BaseDto',
        ];

        $result = $generator->generate('extends', $header, $context);

        expect($result)->toBe('extends BaseDto');
    });

    it('generates extends clause with namespace', function () {
        $generator = new ExtendsHeaderGenerator();
        $context = new DtoGenerationContext();

        $header = [
            'extends' => 'App\\DTO\\BaseDto',
        ];

        $result = $generator->generate('extends', $header, $context);

        expect($result)->toBe('extends App\\DTO\\BaseDto');
    });

    it('handles empty extends value', function () {
        $generator = new ExtendsHeaderGenerator();
        $context = new DtoGenerationContext();

        $header = [
            'extends' => '',
        ];

        $result = $generator->generate('extends', $header, $context);

        expect($result)->toBe('');
    });

    it('handles missing extends key', function () {
        $generator = new ExtendsHeaderGenerator();
        $context = new DtoGenerationContext();

        $header = [];

        $result = $generator->generate('extends', $header, $context);

        expect($result)->toBe('');
    });

    it('handles extends with extra spaces', function () {
        $generator = new ExtendsHeaderGenerator();
        $context = new DtoGenerationContext();

        $header = [
            'extends' => '  BaseDto  ',
        ];

        $result = $generator->generate('extends', $header, $context);

        expect($result)->toBe('extends BaseDto');
    });

    it('handles non-string extends value', function () {
        $generator = new ExtendsHeaderGenerator();
        $context = new DtoGenerationContext();

        $header = [
            'extends' => 123,
        ];

        $result = $generator->generate('extends', $header, $context);

        expect($result)->toBe('');
    });
});
