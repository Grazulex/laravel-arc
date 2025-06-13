<?php

namespace Grazulex\Arc\Exceptions;

class InvalidDTOException extends \InvalidArgumentException
{
    protected array $errors = [];
    protected ?string $property = null;
    protected ?string $expectedType = null;
    protected mixed $actualValue = null;

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function forValidationErrors(array $errors): self
    {
        $exception = new self("DTO validation failed: " . implode(', ', array_keys($errors)));
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

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

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

