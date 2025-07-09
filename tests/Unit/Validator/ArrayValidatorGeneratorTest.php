<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\ArrayValidatorGenerator;

describe('ArrayValidatorGenerator', function () {
    it('supports array type', function () {
        $generator = new ArrayValidatorGenerator();

        expect($generator->supports('array'))->toBeTrue();
        expect($generator->supports('string'))->toBeFalse();
    });

    it('generates array rule with required and custom rules', function () {
        $generator = new ArrayValidatorGenerator();

        $rules = $generator->generate('tags', [
            'type' => 'array',
            'rules' => ['min:1', 'max:5', 'distinct'],
        ]);

        expect($rules)->toBe([
            'tags' => ['required', 'array', 'min:1', 'max:5', 'distinct'],
        ]);
    });

    it('generates array rule without required if nullable is true', function () {
        $generator = new ArrayValidatorGenerator();

        $rules = $generator->generate('tags', [
            'type' => 'array',
            'nullable' => true,
        ]);

        expect($rules)->toBe([
            'tags' => ['array'],
        ]);
    });

    it('returns empty array if type does not match', function () {
        $generator = new ArrayValidatorGenerator();

        $rules = $generator->generate('tags', [
            'type' => 'string',
        ]);

        expect($rules)->toBe([]);
    });
});
