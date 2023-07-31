<?php

namespace Basilicom\ImportDataValidator\Validator\Result;

class ValidationResultFactory
{
    /**
     * @param array $errors
     * @return ValidationResult
     */
    public function getWithErrors(array $errors): ValidationResult
    {
        return new ValidationResult($errors);
    }
}
