<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Contracts\FieldGenerator;
use Grazulex\LaravelArc\Contracts\RelationGenerator;
use Grazulex\LaravelArc\Contracts\ValidatorGenerator;
use Grazulex\LaravelArc\Exceptions\DtoGenerationException;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\DtoGenerator;
use Grazulex\LaravelArc\Generator\FieldGeneratorRegistry;
use Grazulex\LaravelArc\Generator\HeaderGeneratorRegistry;
use Grazulex\LaravelArc\Generator\OptionGeneratorRegistry;
use Grazulex\LaravelArc\Generator\RelationGeneratorRegistry;
use Grazulex\LaravelArc\Generator\ValidatorGeneratorRegistry;

it('handles exception propagation from field generator', function () {
    $context = new DtoGenerationContext();

    // Create a field generator that throws a DtoGenerationException
    $fieldGenerator = new class implements FieldGenerator
    {
        public function supports(string $type): bool
        {
            return $type === 'error_field';
        }

        public function generate(string $name, array $definition, DtoGenerationContext $context): string
        {
            throw DtoGenerationException::unsupportedFieldType('original.yaml', 'original_field', 'error_field', 'OriginalDto');
        }
    };

    $fields = new FieldGeneratorRegistry([$fieldGenerator], $context);
    $validators = new ValidatorGeneratorRegistry([], $context);
    $relations = new RelationGeneratorRegistry([], $context);
    $options = new OptionGeneratorRegistry([], $context);
    $headers = new HeaderGeneratorRegistry([], $context);

    $generator = new DtoGenerator($headers, $fields, $relations, $validators, $options);

    $definition = [
        'dto' => ['class' => 'TestDto'],
        'fields' => [
            'test_field' => ['type' => 'error_field'],
        ],
    ];

    expect(fn () => $generator->generateFromDefinition($definition, 'test.yaml'))
        ->toThrow(DtoGenerationException::class, 'Unsupported field type');
});

it('handles exception re-throwing with context for field generation', function () {
    $context = new DtoGenerationContext();

    // Create a field generator that throws a DtoGenerationException without context
    $fieldGenerator = new class implements FieldGenerator
    {
        public function supports(string $type): bool
        {
            return $type === 'no_context';
        }

        public function generate(string $name, array $definition, DtoGenerationContext $context): string
        {
            throw DtoGenerationException::unsupportedFieldType('', '', 'no_context', '');
        }
    };

    $fields = new FieldGeneratorRegistry([$fieldGenerator], $context);
    $validators = new ValidatorGeneratorRegistry([], $context);
    $relations = new RelationGeneratorRegistry([], $context);
    $options = new OptionGeneratorRegistry([], $context);
    $headers = new HeaderGeneratorRegistry([], $context);

    $generator = new DtoGenerator($headers, $fields, $relations, $validators, $options);

    $definition = [
        'dto' => ['class' => 'TestDto'],
        'fields' => [
            'test_field' => ['type' => 'no_context'],
        ],
    ];

    expect(fn () => $generator->generateFromDefinition($definition, 'test.yaml'))
        ->toThrow(DtoGenerationException::class, 'Unsupported field type');
});

it('handles generic exception from field generator with type extraction', function () {
    $context = new DtoGenerationContext();

    // Create a field generator that throws a generic exception
    $fieldGenerator = new class implements FieldGenerator
    {
        public function supports(string $type): bool
        {
            return $type === 'generic_error';
        }

        public function generate(string $name, array $definition, DtoGenerationContext $context): string
        {
            throw new Exception("No generator found for field type 'generic_error'");
        }
    };

    $fields = new FieldGeneratorRegistry([$fieldGenerator], $context);
    $validators = new ValidatorGeneratorRegistry([], $context);
    $relations = new RelationGeneratorRegistry([], $context);
    $options = new OptionGeneratorRegistry([], $context);
    $headers = new HeaderGeneratorRegistry([], $context);

    $generator = new DtoGenerator($headers, $fields, $relations, $validators, $options);

    $definition = [
        'dto' => ['class' => 'TestDto'],
        'fields' => [
            'test_field' => ['type' => 'generic_error'],
        ],
    ];

    expect(fn () => $generator->generateFromDefinition($definition, 'test.yaml'))
        ->toThrow(DtoGenerationException::class, 'Unsupported field type');
});

it('handles relation generation exceptions', function () {
    $context = new DtoGenerationContext();

    // Create a relation generator that throws an exception
    $relationGenerator = new class implements RelationGenerator
    {
        public function supports(string $type): bool
        {
            return $type === 'errorRelation';
        }

        public function generate(string $name, array $definition, DtoGenerationContext $context): string
        {
            throw new Exception('Relation generation failed');
        }
    };

    $fields = new FieldGeneratorRegistry([], $context);
    $validators = new ValidatorGeneratorRegistry([], $context);
    $relations = new RelationGeneratorRegistry([$relationGenerator], $context);
    $options = new OptionGeneratorRegistry([], $context);
    $headers = new HeaderGeneratorRegistry([], $context);

    $generator = new DtoGenerator($headers, $fields, $relations, $validators, $options);

    $definition = [
        'dto' => ['class' => 'TestDto'],
        'relations' => [
            'test_relation' => ['type' => 'errorRelation'],
        ],
    ];

    expect(fn () => $generator->generateFromDefinition($definition, 'test.yaml'))
        ->toThrow(DtoGenerationException::class, 'Relation generation failed');
});

it('handles validation rule generation exceptions', function () {
    $context = new DtoGenerationContext();

    // Create a string field generator
    $fieldGenerator = new class implements FieldGenerator
    {
        public function supports(string $type): bool
        {
            return $type === 'string';
        }

        public function generate(string $name, array $definition, DtoGenerationContext $context): string
        {
            return "public readonly string \${$name};";
        }
    };

    // Create a validator generator that throws an exception
    $validatorGenerator = new class implements ValidatorGenerator
    {
        public function supports(string $type): bool
        {
            return $type === 'string';
        }

        public function generate(string $name, array $definition, DtoGenerationContext $context): array
        {
            throw new Exception('Validation rule generation failed');
        }
    };

    $fields = new FieldGeneratorRegistry([$fieldGenerator], $context);
    $validators = new ValidatorGeneratorRegistry([$validatorGenerator], $context);
    $relations = new RelationGeneratorRegistry([], $context);
    $options = new OptionGeneratorRegistry([], $context);
    $headers = new HeaderGeneratorRegistry([], $context);

    $generator = new DtoGenerator($headers, $fields, $relations, $validators, $options);

    $definition = [
        'header' => ['dto' => 'TestDto'],
        'fields' => [
            'test_field' => ['type' => 'string'],
        ],
    ];

    expect(fn () => $generator->generateFromDefinition($definition, 'test.yaml'))
        ->toThrow(DtoGenerationException::class, 'Validation rule generation failed');
});

it('handles DTO generation when no fields are defined', function () {
    $context = new DtoGenerationContext();

    $fields = new FieldGeneratorRegistry([], $context);
    $validators = new ValidatorGeneratorRegistry([], $context);
    $relations = new RelationGeneratorRegistry([], $context);
    $options = new OptionGeneratorRegistry([], $context);
    $headers = new HeaderGeneratorRegistry([], $context);

    $generator = new DtoGenerator($headers, $fields, $relations, $validators, $options);

    $definition = [
        'header' => ['dto' => 'TestDto'],
        'fields' => [],
    ];

    // Should not throw an exception for empty fields
    $result = $generator->generateFromDefinition($definition, 'test.yaml');

    expect($result)->toBeString();
    expect($result)->not->toBeEmpty();
});
