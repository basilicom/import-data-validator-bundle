<?php

namespace Basilicom\ImportDataValidator\Rules;

use Basilicom\ImportDataValidator\Rules\Error\ColumnsError;
use Basilicom\ImportDataValidator\Rules\Error\FileError;
use Basilicom\ImportDataValidator\Validator\Result\Exception\ValidationErrorException;
use Basilicom\ImportDataValidator\Validator\Result\ValidationErrorFactory;

class ExistingColumnsRule extends AbstractRule
{
    /** @var string[] */
    private array $columnNames;

    /**
     * @param ValidationErrorFactory $validationErrorFactory
     * @param string[] $columnNames
     */
    public function __construct(
        ValidationErrorFactory $validationErrorFactory,
        array $columnNames = []
    ) {
        parent::__construct($validationErrorFactory);

        $this->columnNames = $columnNames;
    }

    public function shouldValidateFile(): bool
    {
        return true;
    }

    public function shouldValidateDataset(): bool
    {
        return false;
    }

    /**
     * @throws ValidationErrorException
     */
    public function validateFile(string $filepath, array $options = []): array
    {
        $handle = fopen($filepath, 'r');

        if (!$handle) {
            return [
                $this->validationErrorFactory->get(
                    FileError::class,
                    null,
                    'Could not open ' . $filepath
                )
            ];
        }

        $separator = $options['separator'] ?? ',';

        $columnNames = fgetcsv($handle, null, $separator);

        fclose($handle);

        $missingColumnNames = [];
        foreach ($this->columnNames as $validateColumnName) {
            if (!in_array($validateColumnName, $columnNames)) {
                $missingColumnNames[] = $validateColumnName;
            }
        }

        $errors = [];

        if (!empty($missingColumnNames)) {
            $errors[] = $this->validationErrorFactory->get(
                ColumnsError::class,
                1,
                'Missing column names: ' . implode(', ', $missingColumnNames)
            );
        }

        return $errors;
    }
}
