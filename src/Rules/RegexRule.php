<?php

namespace Basilicom\ImportDataValidator\Rules;

use Basilicom\ImportDataValidator\Rules\Error\DatasetError;
use Basilicom\ImportDataValidator\Validator\Result\Exception\ValidationErrorException;
use Basilicom\ImportDataValidator\Validator\Result\ValidationErrorFactory;

class RegexRule extends AbstractRule
{
    private bool $canBeEmpty;
    private array $columnNames;
    private string $regex;

    /**
     * @param ValidationErrorFactory $validationErrorFactory
     * @param string $regex
     * @param array $columnNames
     * @param bool $canBeEmpty
     */
    public function __construct(
        ValidationErrorFactory $validationErrorFactory,
        string $regex,
        array $columnNames = [],
        bool $canBeEmpty = false
    ) {
        parent::__construct($validationErrorFactory);

        $this->regex       = $regex;
        $this->columnNames = $columnNames;
        $this->canBeEmpty  = $canBeEmpty;
    }

    public function shouldValidateFile(): bool
    {
        return false;
    }

    public function shouldValidateDataset(): bool
    {
        return true;
    }

    /**
     * @throws ValidationErrorException
     */
    public function validateDataset(array $dataset, int $lineNumber): array
    {
        $errors = [];
        foreach ($this->columnNames as $columnName) {
            if (!isset($dataset[$columnName])) {
                $errors[] = $this->validationErrorFactory->get(
                    DatasetError::class,
                    $lineNumber,
                    'Missing column: ' . $columnName
                );

                continue;
            }

            $value = $dataset[$columnName];
            if (empty($value)) {
                if (!$this->canBeEmpty) {
                    $errors[] = $this->validationErrorFactory->get(
                        DatasetError::class,
                        $lineNumber,
                        'Column ' . $columnName . ' should not be empty'
                    );
                }

                continue;
            }

            if (!preg_match('/' . $this->regex . '/', $value)) {
                $errors[] = $this->validationErrorFactory->get(
                    DatasetError::class,
                    $lineNumber,
                    'Value (Column "' . $columnName . '") "' . $value . '" does not match regex: ' . $this->regex
                );
            }
        }

        return $errors;
    }
}
