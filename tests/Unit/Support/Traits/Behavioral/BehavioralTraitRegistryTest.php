<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Traits\Behavioral;

use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;
use Grazulex\LaravelArc\Support\Traits\Behavioral\HasSoftDeletesInfo;
use Grazulex\LaravelArc\Support\Traits\Behavioral\HasTimestampsInfo;
use Grazulex\LaravelArc\Support\Traits\Behavioral\HasUuidInfo;
use InvalidArgumentException;
use ReflectionClass;
use stdClass;

describe('BehavioralTraitRegistry', function () {
    beforeEach(function () {
        // Reset registry for each test
        $reflection = new ReflectionClass(BehavioralTraitRegistry::class);
        $property = $reflection->getProperty('traits');
        $property->setAccessible(true);
        $property->setValue([]);
    });

    describe('registration', function () {
        it('registers default traits', function () {
            BehavioralTraitRegistry::registerDefaults();

            $traits = BehavioralTraitRegistry::getTraits();

            expect($traits)->toHaveKey('HasSoftDeletes')
                ->and($traits)->toHaveKey('HasTimestamps')
                ->and($traits)->toHaveKey('HasUuid');
        });

        it('can register custom traits', function () {
            BehavioralTraitRegistry::register('HasSoftDeletes', HasSoftDeletesInfo::class);

            expect(BehavioralTraitRegistry::hasTrail('HasSoftDeletes'))->toBeTrue();
        });

        it('throws exception for invalid trait class', function () {
            expect(fn () => BehavioralTraitRegistry::register('Invalid', stdClass::class))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('trait info retrieval', function () {
        beforeEach(function () {
            BehavioralTraitRegistry::registerDefaults();
        });

        it('returns trait info for registered traits', function () {
            $traitInfoClass = BehavioralTraitRegistry::getTraitInfo('HasSoftDeletes');

            expect($traitInfoClass)->toBe(HasSoftDeletesInfo::class);
        });

        it('throws exception for unregistered traits', function () {
            expect(fn () => BehavioralTraitRegistry::getTraitInfo('NonExistent'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('returns available trait names', function () {
            $names = BehavioralTraitRegistry::getAvailableTraits();

            expect($names)->toContain('HasSoftDeletes')
                ->and($names)->toContain('HasTimestamps')
                ->and($names)->toContain('HasUuid');
        });
    });

    describe('fields and rules aggregation', function () {
        beforeEach(function () {
            BehavioralTraitRegistry::registerDefaults();
        });

        it('aggregates fields from multiple traits', function () {
            $fields = BehavioralTraitRegistry::getFieldsForTraits(['HasSoftDeletes', 'HasTimestamps']);

            expect($fields)->toHaveKey('deleted_at')
                ->and($fields)->toHaveKey('created_at')
                ->and($fields)->toHaveKey('updated_at');
        });

        it('aggregates validation rules from multiple traits', function () {
            $rules = BehavioralTraitRegistry::getValidationRulesForTraits(['HasSoftDeletes', 'HasUuid']);

            expect($rules)->toHaveKey('deleted_at')
                ->and($rules)->toHaveKey('id'); // Changed from uuid to id
        });

        it('aggregates use statements from multiple traits', function () {
            $useStatements = BehavioralTraitRegistry::getUseStatementsForTraits(['HasTimestamps', 'HasUuid']);

            expect($useStatements)->toContain('Carbon\Carbon')
                ->and($useStatements)->toContain('Illuminate\Support\Str');
        });

        it('removes duplicate use statements', function () {
            $useStatements = BehavioralTraitRegistry::getUseStatementsForTraits(['HasSoftDeletes', 'HasTimestamps']);

            $carbonCount = array_count_values($useStatements)['Carbon\Carbon'] ?? 0;
            expect($carbonCount)->toBe(1);
        });
    });
});

describe('HasSoftDeletesInfo', function () {
    it('provides correct trait metadata', function () {
        $info = new HasSoftDeletesInfo();

        expect($info->getTraitName())->toBe('HasSoftDeletes')
            ->and($info->getTraitFields())->toHaveKey('deleted_at')
            ->and($info->getTraitUseStatements())->toContain('Carbon\Carbon')
            ->and($info->getTraitValidationRules())->toHaveKey('deleted_at');
    });
});

describe('HasTimestampsInfo', function () {
    it('provides correct trait metadata', function () {
        $info = new HasTimestampsInfo();

        expect($info->getTraitName())->toBe('HasTimestamps')
            ->and($info->getTraitFields())->toHaveKeys(['created_at', 'updated_at'])
            ->and($info->getTraitUseStatements())->toContain('Carbon\Carbon')
            ->and($info->getTraitValidationRules())->toHaveKeys(['created_at', 'updated_at']);
    });
});

describe('HasUuidInfo', function () {
    it('provides correct trait metadata', function () {
        $info = new HasUuidInfo();

        expect($info->getTraitName())->toBe('HasUuid')
            ->and($info->getTraitFields())->toHaveKey('id') // Changed from uuid to id
            ->and($info->getTraitUseStatements())->toContain('Illuminate\Support\Str')
            ->and($info->getTraitValidationRules())->toHaveKey('id'); // Changed from uuid to id
    });
});
