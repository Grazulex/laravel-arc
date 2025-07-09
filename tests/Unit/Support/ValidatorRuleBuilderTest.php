<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\ValidatorRuleBuilder;

describe('ValidatorRuleBuilder', function () {
    it('returns default rules if no extras defined', function () {
        $rules = ValidatorRuleBuilder::build(['string'], []);

        expect($rules)->toBe(['string', 'required']);
    });

    it('adds required if marked in definition and not already present', function () {
        $rules = ValidatorRuleBuilder::build(['string'], ['required' => true]);

        expect($rules)->toBe(['string', 'required']);
    });

    it('does not add duplicate required if already present', function () {
        $rules = ValidatorRuleBuilder::build(['string', 'required'], ['required' => true]);

        expect($rules)->toBe(['string', 'required']);
    });

    it('merges user-defined rules without duplicates', function () {
        $definition = [
            'rules' => ['max:255', 'regex:/[a-z]/'],
        ];

        $rules = ValidatorRuleBuilder::build(['string'], $definition);

        expect($rules)->toBe(['string', 'required', 'max:255', 'regex:/[a-z]/']);
    });

    it('does not add duplicate user rules', function () {
        $definition = [
            'rules' => ['string', 'max:255'],
        ];

        $rules = ValidatorRuleBuilder::build(['string'], $definition);

        expect($rules)->toBe(['string', 'required', 'max:255']);
    });
});
