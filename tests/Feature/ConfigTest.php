<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\DtoPaths;
use Illuminate\Support\Facades\Config;

describe('Config', function () {
    beforeEach(function () {
        Config::set('dto.definitions_path', base_path('tests/stubs/dto_definitions'));
        Config::set('dto.output_path', base_path('tests/stubs/dto_output'));
    });

    it('loads the dto configuration correctly', function () {
        expect(Config::get('dto'))->toBeArray()
            ->and(Config::get('dto.definitions_path'))->toBe(base_path('tests/stubs/dto_definitions'))
            ->and(Config::get('dto.output_path'))->toBe(base_path('tests/stubs/dto_output'));
    });

    it('can resolve paths via DtoPaths helper', function () {
        expect(DtoPaths::definitionDir())->toBe(base_path('tests/stubs/dto_definitions'))
            ->and(DtoPaths::dtoOutputDir())->toBe(base_path('tests/stubs/dto_output'));
    });

    it('automatically infers namespace from output path', function () {
        $expected = 'Tests\\Stubs\\DtoOutput';

        expect(DtoPaths::dtoNamespace())->toBe($expected);
    });

    it('allows manual override of namespace via config', function () {
        Config::set('dto.namespace', 'Custom\\Dto\\Namespace');

        expect(DtoPaths::dtoNamespace())->toBe('Custom\\Dto\\Namespace');
    });
});
