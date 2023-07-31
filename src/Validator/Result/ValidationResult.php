<?php

namespace Basilicom\ImportDataValidator\Validator\Result;

class ValidationResult
{
    /** @var AbstractValidationError[] */
    private array $errors;

    /**
     * @param AbstractValidationError[] $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return AbstractValidationError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }
}
