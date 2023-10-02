<?php

namespace Basilicom\ImportDataValidator\Validator\Result;

class ValidationResultFactory
{
    /**
     * @param AbstractValidationError[] $errors
     * @return ValidationResult
     */
    public function getWithErrors(array $errors): ValidationResult
    {
        return new ValidationResult($errors);
    }
}
