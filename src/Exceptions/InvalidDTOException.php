<?php

namespace Grazulex\Arc\Exceptions;

use InvalidArgumentException;
use Throwable;

class InvalidDTOException extends InvalidArgumentException
{
    /**
     * @var array<string, array<string>>
     */
    protected array $errors = [];
    protected ?string $property = null;
    protected ?string $expectedType = null;
    protected mixed $actualValue = null;

    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param array<string, array<string>> $errors
     */
    public static function forValidationErrors(array $errors): self
    {
        $exception = new self('DTO validation failed: ' . implode(', ', array_keys($errors)));

        return $exception->setErrors($errors);
    }

    public static function forTypeError(string $property, string $expectedType, mixed $actualValue): self
    {
        $actualType = get_debug_type($actualValue);
        $message = "Property '{$property}' expects type '{$expectedType}', got '{$actualType}'";

        $exception = new self($message);
        $exception->property = $property;
        $exception->expectedType = $expectedType;
        $exception->actualValue = $actualValue;

        return $exception;
    }

    public static function forRequiredProperty(string $property): self
    {
        return new self("Required property '{$property}' is missing");
    }

    public static function forCastingError(string $castType, mixed $value, string $reason): self
    {
        $valueType = get_debug_type($value);
        $message = "Failed to cast value of type '{$valueType}' to '{$castType}': {$reason}";
        
        return new self($message);
    }

    /**
     * @param array<string, array<string>> $errors
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return array<string, array<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getProperty(): ?string
    {
        return $this->property;
    }

    public function getExpectedType(): ?string
    {
        return $this->expectedType;
    }

    public function getActualValue(): mixed
    {
        return $this->actualValue;
    }
}
