<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Generator;

use Grazulex\LaravelArc\Generator\Fields\ArrayFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\BooleanFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\DateFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\DateTimeFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\DecimalFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\DtoFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\EnumFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\FloatFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\IdFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\IntegerFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\JsonFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\StringFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\TextFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\TimeFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\UuidFieldGenerator;
use Grazulex\LaravelArc\Generator\Headers\DtoHeaderGenerator;
use Grazulex\LaravelArc\Generator\Headers\ExtendsHeaderGenerator;
use Grazulex\LaravelArc\Generator\Headers\ModelHeaderGenerator;
use Grazulex\LaravelArc\Generator\Headers\TableHeaderGenerator;
use Grazulex\LaravelArc\Generator\Headers\UseHeaderGenerator;
use Grazulex\LaravelArc\Generator\Options\SoftDeletesOptionGenerator;
use Grazulex\LaravelArc\Generator\Options\TimestampsOptionGenerator;
use Grazulex\LaravelArc\Generator\Relations\BelongsToManyRelationGenerator;
use Grazulex\LaravelArc\Generator\Relations\BelongsToRelationGenerator;
use Grazulex\LaravelArc\Generator\Relations\HasManyRelationGenerator;
use Grazulex\LaravelArc\Generator\Relations\HasOneRelationGenerator;
use Grazulex\LaravelArc\Generator\Validators\ArrayValidatorGenerator;
use Grazulex\LaravelArc\Generator\Validators\BooleanValidatorGenerator;
use Grazulex\LaravelArc\Generator\Validators\DateTimeValidatorGenerator;
use Grazulex\LaravelArc\Generator\Validators\DtoValidatorGenerator;
use Grazulex\LaravelArc\Generator\Validators\EnumValidatorGenerator;
use Grazulex\LaravelArc\Generator\Validators\FloatValidatorGenerator;
use Grazulex\LaravelArc\Generator\Validators\IntegerValidatorGenerator;
use Grazulex\LaravelArc\Generator\Validators\StringValidatorGenerator;
use Grazulex\LaravelArc\Generator\Validators\UuidValidatorGenerator;

final class DtoGenerationContext
{
    private int $maxDepth = 3;

    private array $currentPath = [];

    public function __construct(int $maxDepth = 3)
    {
        $this->maxDepth = $maxDepth;
    }

    public function canNestDto(string $dtoName): bool
    {
        // Vérifier la profondeur
        if (count($this->currentPath) >= $this->maxDepth) {
            return false;
        }

        // Vérifier les cycles
        return ! in_array($dtoName, $this->currentPath);
    }

    public function enterDto(string $dtoName): void
    {
        $this->currentPath[] = $dtoName;
    }

    public function exitDto(): void
    {
        array_pop($this->currentPath);
    }

    public function getCurrentDepth(): int
    {
        return count($this->currentPath);
    }

    public function headers(): HeaderGeneratorRegistry
    {
        return new HeaderGeneratorRegistry([
            'dto' => new DtoHeaderGenerator(),
            'model' => new ModelHeaderGenerator(),
            'table' => new TableHeaderGenerator(),
            'use' => new UseHeaderGenerator(),
            'extends' => new ExtendsHeaderGenerator(),
        ]);
    }

    public function fields(): FieldGeneratorRegistry
    {
        return new FieldGeneratorRegistry([
            new StringFieldGenerator(),
            new ArrayFieldGenerator(),
            new BooleanFieldGenerator(),
            new DateFieldGenerator(),
            new DateTimeFieldGenerator(),
            new DecimalFieldGenerator(),
            new DtoFieldGenerator(),
            new EnumFieldGenerator(),
            new FloatFieldGenerator(),
            new IdFieldGenerator(),
            new IntegerFieldGenerator(),
            new JsonFieldGenerator(),
            new TextFieldGenerator(),
            new TimeFieldGenerator(),
            new UuidFieldGenerator(),
        ], $this);
    }

    public function relations(): RelationGeneratorRegistry
    {
        return new RelationGeneratorRegistry([
            new HasOneRelationGenerator(),
            new HasManyRelationGenerator(),
            new BelongsToRelationGenerator(),
            new BelongsToManyRelationGenerator(),
        ], $this);
    }

    public function validators(): ValidatorGeneratorRegistry
    {
        return new ValidatorGeneratorRegistry([
            new StringValidatorGenerator(),
            new IntegerValidatorGenerator(),
            new FloatValidatorGenerator(),
            new BooleanValidatorGenerator(),
            new UuidValidatorGenerator(),
            new EnumValidatorGenerator(),
            new DateTimeValidatorGenerator(),
            new ArrayValidatorGenerator(),
            new DtoValidatorGenerator(),
        ], $this);
    }

    public function options(): OptionGeneratorRegistry
    {
        return new OptionGeneratorRegistry([
            new TimestampsOptionGenerator(),
            new SoftDeletesOptionGenerator(),
        ]);
    }
}
