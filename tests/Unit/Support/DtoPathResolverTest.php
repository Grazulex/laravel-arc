<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\DtoPathResolver;
use Grazulex\LaravelArc\Support\DtoPaths;
use Illuminate\Support\Facades\Config;

describe('DtoPathResolver', function () {
    beforeEach(function () {
        // Reset config before each test
        Config::set('dto.definitions_path', null);
        Config::set('dto.output_path', null);
        Config::set('dto.namespace', null);
    });

    describe('resolveOutputPath', function () {
        it('resolves path for exact base namespace match', function () {
            Config::set('dto.output_path', base_path('app/DTOs'));
            Config::set('dto.namespace', 'App\\DTOs');

            $path = DtoPathResolver::resolveOutputPath('UserDTO', 'App\\DTOs');

            expect($path)->toBe(base_path('app/DTOs/UserDTO.php'));
        });

        it('resolves path for sub-namespace of base namespace', function () {
            Config::set('dto.output_path', base_path('app/DTOs'));
            Config::set('dto.namespace', 'App\\DTOs');

            $path = DtoPathResolver::resolveOutputPath('AdminUserDTO', 'App\\DTOs\\Admin');

            expect($path)->toBe(base_path('app/DTOs/Admin/AdminUserDTO.php'));
        });

        it('resolves path for deeply nested sub-namespace', function () {
            Config::set('dto.output_path', base_path('app/DTOs'));
            Config::set('dto.namespace', 'App\\DTOs');

            $path = DtoPathResolver::resolveOutputPath('ReportDTO', 'App\\DTOs\\Admin\\Reports');

            expect($path)->toBe(base_path('app/DTOs/Admin/Reports/ReportDTO.php'));
        });

        it('resolves path for completely different namespace', function () {
            Config::set('dto.output_path', base_path('app/DTOs'));
            Config::set('dto.namespace', 'App\\DTOs');

            $path = DtoPathResolver::resolveOutputPath('CustomDTO', 'App\\Custom\\Data');

            expect($path)->toBe(base_path('app/Custom/Data/CustomDTO.php'));
        });

        it('resolves path for non-App namespace', function () {
            Config::set('dto.output_path', base_path('app/DTOs'));
            Config::set('dto.namespace', 'App\\DTOs');

            $path = DtoPathResolver::resolveOutputPath('LibraryDTO', 'Library\\DTOs');

            expect($path)->toBe(base_path('Library/DTOs/LibraryDTO.php'));
        });

        it('works with derived namespace from path', function () {
            Config::set('dto.output_path', base_path('app/Data/DTOs'));
            // Let namespace be derived from path

            $baseNamespace = DtoPaths::dtoNamespace(); // Should be App\Data\DTOs
            $path = DtoPathResolver::resolveOutputPath('UserDTO', $baseNamespace);

            expect($path)->toBe(base_path('app/Data/DTOs/UserDTO.php'));
        });
    });

    describe('resolveNamespaceFromPath', function () {
        it('derives namespace from app directory path', function () {
            $filePath = base_path('app/DTOs/UserDTO.php');
            $namespace = DtoPathResolver::resolveNamespaceFromPath($filePath);

            expect($namespace)->toBe('App\\DTOs');
        });

        it('derives namespace from nested path', function () {
            $filePath = base_path('app/Data/DTOs/Admin/UserDTO.php');
            $namespace = DtoPathResolver::resolveNamespaceFromPath($filePath);

            expect($namespace)->toBe('App\\Data\\DTOs\\Admin');
        });

        it('derives namespace from custom path', function () {
            $filePath = base_path('src/Custom/Data/ProductDTO.php');
            $namespace = DtoPathResolver::resolveNamespaceFromPath($filePath);

            expect($namespace)->toBe('Src\\Custom\\Data');
        });

        it('handles Windows-style paths', function () {
            $filePath = str_replace('/', '\\', base_path('app\\DTOs\\UserDTO.php'));
            $namespace = DtoPathResolver::resolveNamespaceFromPath($filePath);

            expect($namespace)->toBe('App\\DTOs');
        });
    });

    describe('isValidNamespace', function () {
        it('accepts valid namespaces', function () {
            expect(DtoPathResolver::isValidNamespace('App\\DTOs'))->toBeTrue();
            expect(DtoPathResolver::isValidNamespace('App\\Data\\DTOs'))->toBeTrue();
            expect(DtoPathResolver::isValidNamespace('MyCompany\\Project\\DTOs'))->toBeTrue();
            expect(DtoPathResolver::isValidNamespace('Simple'))->toBeTrue();
            expect(DtoPathResolver::isValidNamespace('_Underscore\\Name'))->toBeTrue();
        });

        it('rejects invalid namespaces', function () {
            expect(DtoPathResolver::isValidNamespace(''))->toBeFalse();
            expect(DtoPathResolver::isValidNamespace('App\\\\DTOs'))->toBeFalse(); // consecutive backslashes
            expect(DtoPathResolver::isValidNamespace('App\\'))->toBeFalse(); // trailing backslash
            expect(DtoPathResolver::isValidNamespace('\\App\\DTOs'))->toBeFalse(); // leading backslash
            expect(DtoPathResolver::isValidNamespace('App\\123Invalid'))->toBeFalse(); // starts with number
            expect(DtoPathResolver::isValidNamespace('App\\Invalid-Name'))->toBeFalse(); // contains hyphen
            expect(DtoPathResolver::isValidNamespace('App\\Invalid Space'))->toBeFalse(); // contains space
        });
    });

    describe('normalizeNamespace', function () {
        it('trims whitespace and backslashes', function () {
            expect(DtoPathResolver::normalizeNamespace('  App\\DTOs  '))->toBe('App\\DTOs');
            expect(DtoPathResolver::normalizeNamespace('\\App\\DTOs\\'))->toBe('App\\DTOs');
            expect(DtoPathResolver::normalizeNamespace('  \\App\\DTOs\\  '))->toBe('App\\DTOs');
        });

        it('handles already normalized namespaces', function () {
            expect(DtoPathResolver::normalizeNamespace('App\\DTOs'))->toBe('App\\DTOs');
        });
    });

    describe('isSubNamespaceOf', function () {
        it('identifies sub-namespaces correctly', function () {
            expect(DtoPathResolver::isSubNamespaceOf('App\\DTOs\\Admin', 'App\\DTOs'))->toBeTrue();
            expect(DtoPathResolver::isSubNamespaceOf('App\\DTOs\\Admin\\Users', 'App\\DTOs'))->toBeTrue();
            expect(DtoPathResolver::isSubNamespaceOf('App\\DTOs\\Admin\\Users', 'App\\DTOs\\Admin'))->toBeTrue();
        });

        it('rejects non-sub-namespaces', function () {
            expect(DtoPathResolver::isSubNamespaceOf('App\\DTOs', 'App\\DTOs'))->toBeFalse(); // same namespace
            expect(DtoPathResolver::isSubNamespaceOf('App\\DTOs', 'App\\DTOs\\Admin'))->toBeFalse(); // parent of given
            expect(DtoPathResolver::isSubNamespaceOf('App\\Data', 'App\\DTOs'))->toBeFalse(); // sibling
            expect(DtoPathResolver::isSubNamespaceOf('MyApp\\DTOs', 'App\\DTOs'))->toBeFalse(); // different root
        });

        it('handles normalized input', function () {
            expect(DtoPathResolver::isSubNamespaceOf('\\App\\DTOs\\Admin\\', '\\App\\DTOs\\'))->toBeTrue();
            expect(DtoPathResolver::isSubNamespaceOf('  App\\DTOs\\Admin  ', '  App\\DTOs  '))->toBeTrue();
        });
    });
});
