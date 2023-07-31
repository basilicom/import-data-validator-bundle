<?php

namespace Basilicom\ImportDataValidator\Validator\Result;

use Basilicom\ImportDataValidator\Validator\Result\Exception\ValidationErrorException;
use ReflectionClass;

class ValidationErrorFactory
{
    /**
     * @throws ValidationErrorException
     */
    public function get(string $errorClass, ?int $lineNumber, string $info): AbstractValidationError
    {
        if (!class_exists($errorClass)) {
            throw new ValidationErrorException('Class "' . $errorClass . '" does not exist.');
        }

        // Check if the class extends the specific abstract class
        $reflection = new ReflectionClass($errorClass);
        if (!$reflection->isSubclassOf(AbstractValidationError::class)) {
            throw new ValidationErrorException('Class "' . $errorClass . '" does not extend "' . AbstractValidationError::class . '".');
        }

        return new $errorClass($lineNumber, $info);
    }
}
