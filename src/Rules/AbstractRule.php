<?php

namespace Basilicom\ImportDataValidator\Rules;

use Basilicom\ImportDataValidator\Validator\Result\AbstractValidationError;
use Basilicom\ImportDataValidator\Validator\Result\ValidationErrorFactory;

abstract class AbstractRule
{
    protected ValidationErrorFactory $validationErrorFactory;

    public function __construct(ValidationErrorFactory $validationErrorFactory)
    {
        $this->validationErrorFactory = $validationErrorFactory;
    }

    abstract public function shouldValidateFile(): bool;
    abstract public function shouldValidateDataset(): bool;

    /**
     * @param string $filepath
     * @param array $options
     * @return AbstractValidationError[]
     */
    public function validateFile(string $filepath, array $options = []): array
    {
        return [];
    }

    /**
     * @param array $dataset
     * @param int $lineNumber
     * @return AbstractValidationError[]
     */
    public function validateDataset(array $dataset, int $lineNumber): array
    {
        return [];
    }
}
