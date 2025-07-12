<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Exceptions;

use Exception;
use Throwable;

/**
 * Exception thrown during DTO generation process.
 *
 * This exception provides enhanced error information for CLI feedback
 * and better debugging of YAML parsing and DTO generation failures.
 */
final class DtoGenerationException extends Exception
{
    private ?string $yamlFile = null;

    private ?string $dtoName = null;

    private ?string $fieldName = null;

    private ?string $fieldType = null;

    private ?string $context = null;

    private array $suggestions = [];

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for YAML parsing errors.
     */
    public static function yamlParsingError(string $yamlFile, string $error, ?Throwable $previous = null): self
    {
        $exception = new self("YAML parsing failed: {$error}", 1001, $previous);
        $exception->yamlFile = $yamlFile;
        $exception->context = 'YAML Parsing';
        $exception->suggestions = [
            'Check YAML syntax and indentation',
            'Verify all required sections are present (header, fields)',
            'Ensure proper YAML formatting',
        ];

        return $exception;
    }

    /**
     * Create exception for missing required header information.
     */
    public static function missingHeader(string $yamlFile, string $headerKey): self
    {
        $exception = new self("Missing required header '{$headerKey}' in YAML definition", 1002);
        $exception->yamlFile = $yamlFile;
        $exception->context = 'Header Validation';
        $exception->suggestions = [
            "Add '{$headerKey}' to the header section",
            'Check the YAML schema documentation',
            'Verify header section structure',
        ];

        return $exception;
    }

    /**
     * Create exception for invalid field configuration.
     */
    public static function invalidField(string $yamlFile, string $fieldName, string $error, ?string $dtoName = null): self
    {
        $exception = new self("Invalid field '{$fieldName}': {$error}", 1003);
        $exception->yamlFile = $yamlFile;
        $exception->dtoName = $dtoName;
        $exception->fieldName = $fieldName;
        $exception->context = 'Field Configuration';
        $exception->suggestions = [
            'Check field type and configuration',
            'Verify field attributes are valid',
            'See field types documentation',
        ];

        return $exception;
    }

    /**
     * Create exception for unsupported field type.
     */
    public static function unsupportedFieldType(string $yamlFile, string $fieldName, string $fieldType, ?string $dtoName = null): self
    {
        $exception = new self("Unsupported field type '{$fieldType}' for field '{$fieldName}'", 1004);
        $exception->yamlFile = $yamlFile;
        $exception->dtoName = $dtoName;
        $exception->fieldName = $fieldName;
        $exception->fieldType = $fieldType;
        $exception->context = 'Field Type';
        $exception->suggestions = [
            'Use a supported field type (string, integer, float, boolean, array, etc.)',
            'Check the field types documentation',
            'Verify spelling of field type',
        ];

        return $exception;
    }

    /**
     * Create exception for namespace resolution errors.
     */
    public static function namespaceResolutionError(string $yamlFile, string $namespace, string $error, ?string $dtoName = null): self
    {
        $exception = new self("Namespace resolution failed for '{$namespace}': {$error}", 1005);
        $exception->yamlFile = $yamlFile;
        $exception->dtoName = $dtoName;
        $exception->context = 'Namespace Resolution';
        $exception->suggestions = [
            'Check namespace format and validity',
            'Verify namespace follows PHP standards',
            'Ensure proper directory structure',
        ];

        return $exception;
    }

    /**
     * Create exception for file writing errors.
     */
    public static function fileWriteError(string $yamlFile, string $outputPath, string $error, ?string $dtoName = null): self
    {
        $exception = new self("Failed to write DTO file to '{$outputPath}': {$error}", 1006);
        $exception->yamlFile = $yamlFile;
        $exception->dtoName = $dtoName;
        $exception->context = 'File Writing';
        $exception->suggestions = [
            'Check file permissions',
            'Verify directory exists and is writable',
            'Check available disk space',
        ];

        return $exception;
    }

    /**
     * Create exception for validation rule errors.
     */
    public static function validationRuleError(string $yamlFile, string $fieldName, string $error, ?string $dtoName = null): self
    {
        $exception = new self("Validation rule error for field '{$fieldName}': {$error}", 1007);
        $exception->yamlFile = $yamlFile;
        $exception->dtoName = $dtoName;
        $exception->fieldName = $fieldName;
        $exception->context = 'Validation Rules';
        $exception->suggestions = [
            'Check validation rule syntax',
            'Verify rule compatibility with field type',
            'See Laravel validation documentation',
        ];

        return $exception;
    }

    /**
     * Create exception for circular dependency errors.
     */
    public static function circularDependency(string $yamlFile, string $dtoName, array $dependencyChain): self
    {
        $chain = implode(' -> ', $dependencyChain);
        $exception = new self("Circular dependency detected: {$chain}", 1008);
        $exception->yamlFile = $yamlFile;
        $exception->dtoName = $dtoName;
        $exception->context = 'Circular Dependencies';
        $exception->suggestions = [
            'Remove circular references between DTOs',
            'Consider using array type instead of DTO type',
            'Restructure DTO relationships',
        ];

        return $exception;
    }

    /**
     * Create exception for enum configuration errors.
     */
    public static function enumConfigurationError(string $yamlFile, string $fieldName, string $error, ?string $dtoName = null): self
    {
        $exception = new self("Enum configuration error for field '{$fieldName}': {$error}", 1009);
        $exception->yamlFile = $yamlFile;
        $exception->dtoName = $dtoName;
        $exception->fieldName = $fieldName;
        $exception->context = 'Enum Configuration';
        $exception->suggestions = [
            'Check enum class exists and is valid',
            'Verify enum values are properly defined',
            'See enum configuration documentation',
        ];

        return $exception;
    }

    /**
     * Create exception for relation configuration errors.
     */
    public static function relationConfigurationError(string $yamlFile, string $relationName, string $error, ?string $dtoName = null): self
    {
        $exception = new self("Relation configuration error for '{$relationName}': {$error}", 1010);
        $exception->yamlFile = $yamlFile;
        $exception->dtoName = $dtoName;
        $exception->fieldName = $relationName;
        $exception->context = 'Relation Configuration';
        $exception->suggestions = [
            'Check relation type and target model',
            'Verify target model class exists',
            'See relations documentation',
        ];

        return $exception;
    }

    /**
     * Get the YAML file path where the error occurred.
     */
    public function getYamlFile(): ?string
    {
        return $this->yamlFile;
    }

    /**
     * Get the DTO name being generated.
     */
    public function getDtoName(): ?string
    {
        return $this->dtoName;
    }

    /**
     * Get the field name where the error occurred.
     */
    public function getFieldName(): ?string
    {
        return $this->fieldName;
    }

    /**
     * Get the field type that caused the error.
     */
    public function getFieldType(): ?string
    {
        return $this->fieldType;
    }

    /**
     * Get the context of the error.
     */
    public function getContext(): ?string
    {
        return $this->context;
    }

    /**
     * Get suggestions for fixing the error.
     */
    public function getSuggestions(): array
    {
        return $this->suggestions;
    }

    /**
     * Add a suggestion for fixing the error.
     */
    public function addSuggestion(string $suggestion): self
    {
        $this->suggestions[] = $suggestion;

        return $this;
    }

    /**
     * Get formatted error message for CLI output.
     */
    public function getFormattedMessage(): string
    {
        $message = '❌ DTO Generation Error';

        if ($this->context !== null && $this->context !== '' && $this->context !== '0') {
            $message .= " ({$this->context})";
        }

        $message .= "\n\n";
        $message .= "Error: {$this->getMessage()}\n";

        if ($this->yamlFile !== null && $this->yamlFile !== '' && $this->yamlFile !== '0') {
            $message .= "File: {$this->yamlFile}\n";
        }

        if ($this->dtoName !== null && $this->dtoName !== '' && $this->dtoName !== '0') {
            $message .= "DTO: {$this->dtoName}\n";
        }

        if ($this->fieldName !== null && $this->fieldName !== '' && $this->fieldName !== '0') {
            $message .= "Field: {$this->fieldName}\n";
        }

        if ($this->suggestions !== []) {
            $message .= "\n💡 Suggestions:\n";
            foreach ($this->suggestions as $suggestion) {
                $message .= "  • {$suggestion}\n";
            }
        }

        if ($this->getPrevious() instanceof Throwable) {
            $message .= "\n🔍 Technical Details:\n";
            $message .= "  {$this->getPrevious()->getMessage()}\n";
        }

        return $message;
    }
}
