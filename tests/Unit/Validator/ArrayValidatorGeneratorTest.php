<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\ArrayValidatorGenerator;

describe('ArrayValidatorGenerator', function () {
    it('supports array and json types', function () {
        $gen = new ArrayValidatorGenerator();

        expect($gen->supports('array'))->toBeTrue();
        expect($gen->supports('json'))->toBeTrue();
        expect($gen->supports('string'))->toBeFalse();
    });

    it('generates array rule with required', function () {
        $generator = new ArrayValidatorGenerator();

        $rules = $generator->generate('tags', [
            'type' => 'array',
            'required' => true,
        ]);

        expect($rules)->toBe([
            'tags' => ['array', 'required'],
        ]);
    });

    it('returns only array rule when no extras given', function () {
        $generator = new ArrayValidatorGenerator();

        $rules = $generator->generate('tags', ['type' => 'json']);

        expect($rules)->toBe([
            'tags' => ['array'],
        ]);
    });
});
