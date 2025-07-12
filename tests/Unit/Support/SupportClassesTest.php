<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\DefaultValueCaster;

describe('Support Classes Coverage', function () {
    describe('DefaultValueCaster', function () {
        it('casts string values correctly', function () {
            expect(DefaultValueCaster::cast('string', 'hello'))->toBe("'hello'");
            expect(DefaultValueCaster::cast('string', '123'))->toBe("'123'");
        });

        it('casts integer values correctly', function () {
            expect(DefaultValueCaster::cast('integer', 123))->toBe('123');
            expect(DefaultValueCaster::cast('integer', 0))->toBe('0');
            expect(DefaultValueCaster::cast('integer', -456))->toBe('-456');
        });

        it('casts boolean values correctly', function () {
            expect(DefaultValueCaster::cast('boolean', true))->toBe('true');
            expect(DefaultValueCaster::cast('boolean', false))->toBe('false');
        });

        it('casts float values correctly', function () {
            expect(DefaultValueCaster::cast('float', 123.45))->toBe('123.45');
            expect(DefaultValueCaster::cast('float', 0.0))->toBe('0');
            expect(DefaultValueCaster::cast('float', -456.78))->toBe('-456.78');
        });

        it('casts array values correctly', function () {
            expect(DefaultValueCaster::cast('array', []))->toContain('array');
            expect(DefaultValueCaster::cast('array', ['a', 'b']))->toContain('array');
        });

        it('returns null for unknown types', function () {
            expect(DefaultValueCaster::cast('unknown', 'hello'))->toBe('null');
            expect(DefaultValueCaster::cast('custom_type', '123'))->toBe('null');
        });

        it('casts decimal as quoted string', function () {
            expect(DefaultValueCaster::cast('decimal', '123.45'))->toBe("'123.45'");
        });

        it('casts enum as quoted string', function () {
            expect(DefaultValueCaster::cast('enum', 'active'))->toBe("'active'");
        });
    });
});
