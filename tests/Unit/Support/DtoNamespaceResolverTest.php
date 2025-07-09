<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\DtoNamespaceResolver;

describe('DtoNamespaceResolver', function () {
    it('returns FQCN if dto name contains backslash', function () {
        $fqcn = 'App\\Custom\\Namespace\\UserDTO';
        $result = DtoNamespaceResolver::resolveDtoClass($fqcn);

        expect($result)->toBe($fqcn);
    });

    it('builds FQCN from config namespace and simple dto name', function () {
        config()->set('dto.dto_namespace', 'App\\DTO');
        $result = DtoNamespaceResolver::resolveDtoClass('ProductDTO');

        expect($result)->toBe('App\\DTO\\ProductDTO');
    });

    it('handles trailing backslash in config namespace', function () {
        config()->set('dto.dto_namespace', 'App\\DTO\\');
        $result = DtoNamespaceResolver::resolveDtoClass('CategoryDTO');

        expect($result)->toBe('App\\DTO\\CategoryDTO');
    });
});
