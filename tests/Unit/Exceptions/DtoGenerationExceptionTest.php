<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Exceptions\DtoGenerationException;

describe('DtoGenerationException', function () {
    it('creates YAML parsing error with context and suggestions', function () {
        $exception = DtoGenerationException::yamlParsingError(
            '/path/to/user.yaml',
            'Syntax error on line 5'
        );

        expect($exception->getMessage())->toBe('YAML parsing failed: Syntax error on line 5');
        expect($exception->getCode())->toBe(1001);
        expect($exception->getYamlFile())->toBe('/path/to/user.yaml');
        expect($exception->getContext())->toBe('YAML Parsing');
        expect($exception->getSuggestions())->toContain('Check YAML syntax and indentation');
    });

    it('creates missing header error with appropriate context', function () {
        $exception = DtoGenerationException::missingHeader('/path/to/user.yaml', 'dto');

        expect($exception->getMessage())->toBe('Missing required header \'dto\' in YAML definition');
        expect($exception->getCode())->toBe(1002);
        expect($exception->getYamlFile())->toBe('/path/to/user.yaml');
        expect($exception->getContext())->toBe('Header Validation');
        expect($exception->getSuggestions())->toContain('Add \'dto\' to the header section');
    });

    it('creates invalid field error with field context', function () {
        $exception = DtoGenerationException::invalidField(
            '/path/to/user.yaml',
            'email',
            'Invalid validation rule',
            'UserDTO'
        );

        expect($exception->getMessage())->toBe('Invalid field \'email\': Invalid validation rule');
        expect($exception->getCode())->toBe(1003);
        expect($exception->getYamlFile())->toBe('/path/to/user.yaml');
        expect($exception->getDtoName())->toBe('UserDTO');
        expect($exception->getFieldName())->toBe('email');
        expect($exception->getContext())->toBe('Field Configuration');
    });

    it('creates unsupported field type error with suggestions', function () {
        $exception = DtoGenerationException::unsupportedFieldType(
            '/path/to/user.yaml',
            'custom_field',
            'unknown_type',
            'UserDTO'
        );

        expect($exception->getMessage())->toBe('Unsupported field type \'unknown_type\' for field \'custom_field\'');
        expect($exception->getCode())->toBe(1004);
        expect($exception->getFieldName())->toBe('custom_field');
        expect($exception->getSuggestions())->toContain('Use a supported field type (string, integer, float, boolean, array, etc.)');
    });

    it('creates namespace resolution error with context', function () {
        $exception = DtoGenerationException::namespaceResolutionError(
            '/path/to/user.yaml',
            'Invalid\\\\Namespace',
            'Contains double backslashes',
            'UserDTO'
        );

        expect($exception->getMessage())->toBe('Namespace resolution failed for \'Invalid\\\\Namespace\': Contains double backslashes');
        expect($exception->getCode())->toBe(1005);
        expect($exception->getContext())->toBe('Namespace Resolution');
        expect($exception->getSuggestions())->toContain('Check namespace format and validity');
    });

    it('creates file write error with appropriate context', function () {
        $exception = DtoGenerationException::fileWriteError(
            '/path/to/user.yaml',
            '/read-only/UserDTO.php',
            'Permission denied',
            'UserDTO'
        );

        expect($exception->getMessage())->toBe('Failed to write DTO file to \'/read-only/UserDTO.php\': Permission denied');
        expect($exception->getCode())->toBe(1006);
        expect($exception->getContext())->toBe('File Writing');
        expect($exception->getSuggestions())->toContain('Check file permissions');
    });

    it('creates validation rule error with field context', function () {
        $exception = DtoGenerationException::validationRuleError(
            '/path/to/user.yaml',
            'email',
            'Invalid email rule syntax',
            'UserDTO'
        );

        expect($exception->getMessage())->toBe('Validation rule error for field \'email\': Invalid email rule syntax');
        expect($exception->getCode())->toBe(1007);
        expect($exception->getFieldName())->toBe('email');
        expect($exception->getContext())->toBe('Validation Rules');
    });

    it('creates circular dependency error with dependency chain', function () {
        $dependencyChain = ['UserDTO', 'ProfileDTO', 'UserDTO'];
        $exception = DtoGenerationException::circularDependency(
            '/path/to/user.yaml',
            'UserDTO',
            $dependencyChain
        );

        expect($exception->getMessage())->toBe('Circular dependency detected: UserDTO -> ProfileDTO -> UserDTO');
        expect($exception->getCode())->toBe(1008);
        expect($exception->getContext())->toBe('Circular Dependencies');
        expect($exception->getSuggestions())->toContain('Remove circular references between DTOs');
    });

    it('creates enum configuration error with suggestions', function () {
        $exception = DtoGenerationException::enumConfigurationError(
            '/path/to/user.yaml',
            'status',
            'Enum class not found',
            'UserDTO'
        );

        expect($exception->getMessage())->toBe('Enum configuration error for field \'status\': Enum class not found');
        expect($exception->getCode())->toBe(1009);
        expect($exception->getFieldName())->toBe('status');
        expect($exception->getContext())->toBe('Enum Configuration');
        expect($exception->getSuggestions())->toContain('Check enum class exists and is valid');
    });

    it('creates relation configuration error with context', function () {
        $exception = DtoGenerationException::relationConfigurationError(
            '/path/to/user.yaml',
            'posts',
            'Target model not found',
            'UserDTO'
        );

        expect($exception->getMessage())->toBe('Relation configuration error for \'posts\': Target model not found');
        expect($exception->getCode())->toBe(1010);
        expect($exception->getFieldName())->toBe('posts');
        expect($exception->getContext())->toBe('Relation Configuration');
    });

    it('can add additional suggestions', function () {
        $exception = DtoGenerationException::missingHeader('/path/to/user.yaml', 'dto');

        $exception->addSuggestion('Custom suggestion for fixing this issue');

        expect($exception->getSuggestions())->toContain('Custom suggestion for fixing this issue');
    });

    it('formats error message for CLI output', function () {
        $exception = DtoGenerationException::invalidField(
            '/path/to/user.yaml',
            'email',
            'Invalid validation rule',
            'UserDTO'
        );

        $formattedMessage = $exception->getFormattedMessage();

        expect($formattedMessage)->toContain('❌ DTO Generation Error');
        expect($formattedMessage)->toContain('Error: Invalid field \'email\': Invalid validation rule');
        expect($formattedMessage)->toContain('File: /path/to/user.yaml');
        expect($formattedMessage)->toContain('DTO: UserDTO');
        expect($formattedMessage)->toContain('Field: email');
        expect($formattedMessage)->toContain('💡 Suggestions:');
        expect($formattedMessage)->toContain('• Check field type and configuration');
    });

    it('includes technical details when previous exception exists', function () {
        $originalException = new Exception('Original error message');
        $exception = DtoGenerationException::yamlParsingError(
            '/path/to/user.yaml',
            'YAML parsing failed',
            $originalException
        );

        $formattedMessage = $exception->getFormattedMessage();

        expect($formattedMessage)->toContain('🔍 Technical Details:');
        expect($formattedMessage)->toContain('Original error message');
    });

    it('handles minimal error information gracefully', function () {
        $exception = new DtoGenerationException('Simple error message');

        $formattedMessage = $exception->getFormattedMessage();

        expect($formattedMessage)->toContain('❌ DTO Generation Error');
        expect($formattedMessage)->toContain('Error: Simple error message');
        expect($formattedMessage)->not->toContain('File:');
        expect($formattedMessage)->not->toContain('DTO:');
        expect($formattedMessage)->not->toContain('Field:');
    });
});
