<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Grazulex\LaravelArc\Services\ModelSchemaFieldTypeMapper;

describe('ModelSchemaFieldTypeMapper Enhanced', function () {
    beforeEach(function () {
        $this->mapper = new ModelSchemaFieldTypeMapper();
    });

    describe('Geometric Types Mapping', function () {
        it('maps geometric types to string', function () {
            $geometricTypes = [
                'point', 'geometry', 'polygon', 'linestring',
                'multipoint', 'multipolygon', 'multilinestring', 'geometrycollection',
            ];

            foreach ($geometricTypes as $type) {
                expect($this->mapper->mapToArcType($type))
                    ->toBe('string', "Type '$type' should map to 'string'");
            }
        });
    });

    describe('Enhanced String Types Mapping', function () {
        it('maps enhanced string types to string', function () {
            $stringTypes = [
                'email', 'uuid', 'url', 'slug', 'phone', 'color',
                'ip', 'ipv4', 'ipv6', 'mac', 'currency', 'locale', 'timezone',
            ];

            foreach ($stringTypes as $type) {
                expect($this->mapper->mapToArcType($type))
                    ->toBe('string', "Type '$type' should map to 'string'");
            }
        });
    });

    describe('JSON and Array Types Mapping', function () {
        it('maps JSON and array types to array', function () {
            $arrayTypes = ['json', 'jsonb', 'set', 'array', 'collection'];

            foreach ($arrayTypes as $type) {
                expect($this->mapper->mapToArcType($type))
                    ->toBe('array', "Type '$type' should map to 'array'");
            }
        });
    });

    describe('Numeric Types Mapping', function () {
        it('maps decimal types to decimal', function () {
            $decimalTypes = ['float', 'double', 'decimal', 'money'];

            foreach ($decimalTypes as $type) {
                expect($this->mapper->mapToArcType($type))
                    ->toBe('decimal', "Type '$type' should map to 'decimal'");
            }
        });

        it('maps integer types to integer', function () {
            $integerTypes = ['int', 'bigint', 'smallint', 'tinyint', 'unsignedint', 'year'];

            foreach ($integerTypes as $type) {
                expect($this->mapper->mapToArcType($type))
                    ->toBe('integer', "Type '$type' should map to 'integer'");
            }
        });

        it('maps boolean types to boolean', function () {
            $booleanTypes = ['bool', 'boolean'];

            foreach ($booleanTypes as $type) {
                expect($this->mapper->mapToArcType($type))
                    ->toBe('boolean', "Type '$type' should map to 'boolean'");
            }
        });
    });

    describe('Date/Time Types Mapping', function () {
        it('maps datetime types correctly', function () {
            expect($this->mapper->mapToArcType('datetime'))->toBe('datetime');
            expect($this->mapper->mapToArcType('timestamp'))->toBe('datetime');
            expect($this->mapper->mapToArcType('date'))->toBe('date');
            expect($this->mapper->mapToArcType('time'))->toBe('time');
            expect($this->mapper->mapToArcType('softdeletes'))->toBe('datetime');
            expect($this->mapper->mapToArcType('timestamps'))->toBe('datetime');
        });
    });

    describe('Text Types Mapping', function () {
        it('maps text types correctly', function () {
            $textTypes = ['text', 'longtext', 'mediumtext'];
            foreach ($textTypes as $type) {
                expect($this->mapper->mapToArcType($type))->toBe('text');
            }

            $stringTypes = ['varchar', 'char', 'string'];
            foreach ($stringTypes as $type) {
                expect($this->mapper->mapToArcType($type))->toBe('string');
            }
        });
    });

    describe('Binary Types Mapping', function () {
        it('maps binary types to string', function () {
            $binaryTypes = ['binary', 'blob', 'longblob', 'mediumblob'];

            foreach ($binaryTypes as $type) {
                expect($this->mapper->mapToArcType($type))->toBe('string');
            }
        });
    });

    describe('Special Types Mapping', function () {
        it('maps special types correctly', function () {
            $stringSpecialTypes = [
                'enum', 'morphs', 'nullablemorphs', 'uuidmorphs',
                'nullableuuidmorphs', 'remembertokens', 'fulltext', 'spatialindex',
            ];

            foreach ($stringSpecialTypes as $type) {
                expect($this->mapper->mapToArcType($type))->toBe('string');
            }
        });
    });

    describe('Type Coverage', function () {
        it('provides comprehensive type mapping coverage', function () {
            $mappings = $this->mapper->getAllMappings();

            // Test that we have significant coverage (65+ types)
            expect(count($mappings))->toBeGreaterThan(60);

            // Test key categories are covered
            $types = array_keys($mappings);

            // Geometric types
            expect($types)->toContain('point');
            expect($types)->toContain('polygon');
            expect($types)->toContain('geometry');

            // Enhanced string types
            expect($types)->toContain('email');
            expect($types)->toContain('uuid');
            expect($types)->toContain('url');

            // JSON types
            expect($types)->toContain('json');
            expect($types)->toContain('jsonb');
            expect($types)->toContain('set');
        });
    });

    describe('Fallback Behavior', function () {
        it('returns original type for unmapped types', function () {
            $unknownType = 'custom_unknown_type';
            expect($this->mapper->mapToArcType($unknownType))->toBe($unknownType);
        });
    });
});
