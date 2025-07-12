<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Contracts\FieldGenerator;
use Grazulex\LaravelArc\Exceptions\DtoGenerationException;
use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\FieldGeneratorRegistry;

it('throws exception for unsupported field type', function () {
    $context = new DtoGenerationContext();
    $registry = new FieldGeneratorRegistry([], $context);

    expect(fn () => $registry->generate('field', ['type' => 'unsupported_type']))
        ->toThrow(DtoGenerationException::class, 'Unsupported field type');
});

it('handles exception from field generator', function () {
    $context = new DtoGenerationContext();

    // Create a mock field generator that throws an exception
    $generator = new class implements FieldGenerator
    {
        public function supports(string $type): bool
        {
            return $type === 'string'; // Use a known type
        }

        public function generate(string $name, array $definition, DtoGenerationContext $context): string
        {
            throw new Exception('Field generation failed');
        }
    };

    $registry = new FieldGeneratorRegistry([$generator], $context);

    expect(fn () => $registry->generate('field', ['type' => 'string']))
        ->toThrow(DtoGenerationException::class, 'Field generation failed: Field generation failed');
});

it('re-throws existing DtoGenerationException', function () {
    $context = new DtoGenerationContext();

    // Create a mock field generator that throws a DtoGenerationException
    $generator = new class implements FieldGenerator
    {
        public function supports(string $type): bool
        {
            return $type === 'dto'; // Use a known type
        }

        public function generate(string $name, array $definition, DtoGenerationContext $context): string
        {
            throw DtoGenerationException::invalidField('test.yaml', 'test_field', 'Test error');
        }
    };

    $registry = new FieldGeneratorRegistry([$generator], $context);

    expect(fn () => $registry->generate('field', ['type' => 'dto']))
        ->toThrow(DtoGenerationException::class, 'Invalid field \'test_field\': Test error');
});

it('handles missing type in definition', function () {
    $context = new DtoGenerationContext();

    // Create a string field generator
    $generator = new class implements FieldGenerator
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

    $registry = new FieldGeneratorRegistry([$generator], $context);

    // When type is missing, it should default to 'string'
    $result = $registry->generate('test_field', []);

    expect($result)->toBe('public readonly string $test_field;');
});
