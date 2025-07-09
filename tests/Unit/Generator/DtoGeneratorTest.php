<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerator;

describe('DtoGenerator', function () {
    it('can be instantiated with ::make()', function () {
        $generator = DtoGenerator::make();

        expect($generator)->toBeInstanceOf(DtoGenerator::class);
    });
});
