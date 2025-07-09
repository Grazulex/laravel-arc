<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Generator\Fields\ArrayFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\BooleanFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\DateFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\DateTimeFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\EnumFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\FloatFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\IntegerFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\StringFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\UuidFieldGenerator;
use Grazulex\LaravelArc\Generator\Headers\DtoHeaderGenerator;
use Grazulex\LaravelArc\Generator\Headers\ModelHeaderGenerator;
use Grazulex\LaravelArc\Generator\Headers\TableHeaderGenerator;
use Grazulex\LaravelArc\Generator\Relations\BelongsToManyRelationGenerator;
use Grazulex\LaravelArc\Generator\Relations\BelongsToRelationGenerator;
use Grazulex\LaravelArc\Generator\Relations\HasManyRelationGenerator;
use Grazulex\LaravelArc\Generator\Relations\HasOneRelationGenerator;
use Grazulex\LaravelArc\Generator\Validators\EnumValidatorGenerator;

final class DtoGenerationContext
{
    public function headers(): HeaderGeneratorRegistry
    {
        return new HeaderGeneratorRegistry([
            new DtoHeaderGenerator(),
            new ModelHeaderGenerator(),
            new TableHeaderGenerator(),
        ]);
    }

    public function fields(): FieldGeneratorRegistry
    {
        return new FieldGeneratorRegistry([
            new StringFieldGenerator(),
            new IntegerFieldGenerator(),
            new BooleanFieldGenerator(),
            new FloatFieldGenerator(),
            new UuidFieldGenerator(),
            new DateFieldGenerator(),
            new DateTimeFieldGenerator(),
            new ArrayFieldGenerator(),
            new EnumFieldGenerator(),
        ]);
    }

    public function relations(): RelationGeneratorRegistry
    {
        return new RelationGeneratorRegistry([
            new HasOneRelationGenerator(),
            new HasManyRelationGenerator(),
            new BelongsToRelationGenerator(),
            new BelongsToManyRelationGenerator(),
        ]);
    }

    public function validators(): ValidatorGeneratorRegistry
    {
        return new ValidatorGeneratorRegistry([
            new EnumValidatorGenerator(),
        ]);
    }
}
