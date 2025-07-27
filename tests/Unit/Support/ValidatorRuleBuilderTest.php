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

    it('adds nullable if required is false and not already present', function () {
        $rules = ValidatorRuleBuilder::build(['string'], ['required' => false]);

        expect($rules)->toBe(['string', 'nullable']);
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

    it('merges validation field rules from yaml definition', function () {
        $definition = [
            'validation' => ['email', 'unique:users'],
        ];

        $rules = ValidatorRuleBuilder::build(['string'], $definition);

        expect($rules)->toBe(['string', 'required', 'email', 'unique:users']);
    });

    it('handles both validation and rules fields together', function () {
        $definition = [
            'validation' => ['email'],
            'rules' => ['unique:users'],
        ];

        $rules = ValidatorRuleBuilder::build(['string'], $definition);

        expect($rules)->toBe(['string', 'required', 'email', 'unique:users']);
    });

    it('handles nullable with validation rules', function () {
        $definition = [
            'required' => false,
            'validation' => ['min:18', 'max:120'],
        ];

        $rules = ValidatorRuleBuilder::build(['integer'], $definition);

        expect($rules)->toBe(['integer', 'nullable', 'min:18', 'max:120']);
    });

    it('does not duplicate rules from validation field', function () {
        $definition = [
            'validation' => ['required', 'email'],
        ];

        $rules = ValidatorRuleBuilder::build(['string', 'required'], $definition);

        expect($rules)->toBe(['string', 'required', 'email']);
    });

    it('handles empty validation array', function () {
        $definition = [
            'validation' => [],
        ];

        $rules = ValidatorRuleBuilder::build(['string'], $definition);

        expect($rules)->toBe(['string', 'required']);
    });
});
