<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Fields\TextFieldGenerator;

describe('TextFieldGenerator', function () {
    it('supports text type', function () {
        $generator = new TextFieldGenerator();

        expect($generator->supports('text'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates nullable text field with null default', function () {
        $generator = new TextFieldGenerator();

        $code = $generator->generate('content', [
            'nullable' => true,
        ]);

        expect($code)->toBe('public ?string $content = null;');
    });

    it('generates text field with default value', function () {
        $generator = new TextFieldGenerator();

        $code = $generator->generate('content', [
            'default' => 'Hello world',
        ]);

        expect($code)->toContain('public');
        expect($code)->toContain('$content =');
    });
});
