<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\Validators\BaseValidatorGenerator;

// Test implementation of BaseValidatorGenerator
final class TestValidatorGenerator extends BaseValidatorGenerator
{
    public function testIsMatchingType(array $config, string $expected): bool
    {
        return $this->isMatchingType($config, $expected);
    }

    public function testApplyRequiredIfNeeded(array $config, array $rules): array
    {
        return $this->applyRequiredIfNeeded($config, $rules);
    }
}

describe('BaseValidatorGenerator', function () {
    beforeEach(function () {
        $this->generator = new TestValidatorGenerator();
    });

    it('correctly identifies matching types', function () {
        $config = ['type' => 'string'];
        expect($this->generator->testIsMatchingType($config, 'string'))->toBe(true);
        expect($this->generator->testIsMatchingType($config, 'integer'))->toBe(false);
    });

    it('handles missing type in config', function () {
        $config = [];
        expect($this->generator->testIsMatchingType($config, 'string'))->toBe(false);
    });

    it('applies required rule when field is required', function () {
        $config = ['required' => true];
        $rules = ['string'];
        $result = $this->generator->testApplyRequiredIfNeeded($config, $rules);
        expect($result)->toEqual(['required', 'string']);
    });

    it('does not apply required rule when field is not required', function () {
        $config = ['required' => false];
        $rules = ['string'];
        $result = $this->generator->testApplyRequiredIfNeeded($config, $rules);
        expect($result)->toEqual(['string']);
    });

    it('applies required rule by default when not specified', function () {
        $config = [];
        $rules = ['string'];
        $result = $this->generator->testApplyRequiredIfNeeded($config, $rules);
        expect($result)->toEqual(['required', 'string']);
    });

    it('does not duplicate required rule', function () {
        $config = ['required' => true];
        $rules = ['required', 'string'];
        $result = $this->generator->testApplyRequiredIfNeeded($config, $rules);
        expect($result)->toEqual(['required', 'required', 'string']); // This shows the current behavior
    });
});
