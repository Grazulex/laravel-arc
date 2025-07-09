<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\FieldGeneratorRegistry;
use Grazulex\LaravelArc\Generator\HeaderGeneratorRegistry;
use Grazulex\LaravelArc\Generator\RelationGeneratorRegistry;
use Grazulex\LaravelArc\Generator\ValidatorGeneratorRegistry;

describe('DtoGenerationContext', function () {
    beforeEach(function () {
        $this->context = new DtoGenerationContext();
    });

    it('returns a HeaderGeneratorRegistry', function () {
        expect($this->context->headers())->toBeInstanceOf(HeaderGeneratorRegistry::class);
    });

    it('returns a FieldGeneratorRegistry', function () {
        expect($this->context->fields())->toBeInstanceOf(FieldGeneratorRegistry::class);
    });

    it('returns a RelationGeneratorRegistry', function () {
        expect($this->context->relations())->toBeInstanceOf(RelationGeneratorRegistry::class);
    });

    it('returns a ValidatorGeneratorRegistry', function () {
        expect($this->context->validators())->toBeInstanceOf(ValidatorGeneratorRegistry::class);
    });
});
