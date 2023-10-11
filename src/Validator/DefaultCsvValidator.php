<?php

namespace Basilicom\ImportDataValidator\Validator;

use Basilicom\ImportDataValidator\Rules\Error\DatasetError;
use Basilicom\ImportDataValidator\Rules\Error\FileError;
use Basilicom\ImportDataValidator\Validator\Result\Exception\ValidationErrorException;
use Basilicom\ImportDataValidator\Validator\Result\ValidationErrorFactory;
use Basilicom\ImportDataValidator\Validator\Result\ValidationResultFactory;

class DefaultCsvValidator implements ValidatorInterface
{
    private ValidationResultFactory $validationResultFactory;
    private string $separator;
    private ValidationErrorFactory $validationErrorFactory;
    private int $headerLineSize;

    public function __construct(
        ValidationResultFactory $validationResultFactory,
        ValidationErrorFactory $validationErrorFactory,
        string $separator = ',',
        int $headerLineSize = 1
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->validationErrorFactory  = $validationErrorFactory;
        $this->separator               = $separator;
        $this->headerLineSize          = $headerLineSize;
    }

    /**
     * @throws ValidationErrorException
     */
    public function validate(string $filepath, RuleSetInterface $ruleSet): Result\ValidationResult
    {
        $options = [
            'separator' => $this->separator
        ];

        $handle = fopen($filepath, 'r');

        if (!$handle) {
            return $this->validationResultFactory->getWithErrors(
                [
                    $this->validationErrorFactory->get(
                        FileError::class,
                        null,
                        'Could not open ' . $filepath
                    )
                ]
            );
        }

        $errors = [];

        foreach ($ruleSet->getRules() as $rule) {
            if (!$rule->shouldValidateFile()) {
                continue;
            }

            $thisErrors = $rule->validateFile($filepath, $options);
            $errors = array_merge($errors, $thisErrors);
        }

        $columnNames = [];
        for($i = 0; $i < $this->headerLineSize; $i++) {
            $columnNames = fgetcsv($handle, null, $this->separator);
        }
        if (empty($columnNames)) {
            return $this->validationResultFactory->getWithErrors(
                [
                    $this->validationErrorFactory->get(
                        FileError::class,
                        null,
                        'Could not get column names from ' . $filepath
                    )
                ]
            );
        }

        $lineNumber = $this->headerLineSize;
        while($row = fgetcsv($handle, null, $this->separator)) {
            $lineNumber++;
            if (empty($row)) {
                $errors[] = $this->validationErrorFactory->get(
                    DatasetError::class,
                    $lineNumber,
                    'Line is empty or cannot be parsed'
                );

                continue;
            }

            $dataset = $this->createDataset($row, $columnNames);
            foreach ($ruleSet->getRules() as $rule) {
                if (!$rule->shouldValidateDataset()) {
                    continue;
                }

                $thisErrors = $rule->validateDataset($dataset, $lineNumber);
                $errors = array_merge($errors, $thisErrors);
            }
        }

        fclose($handle);

        return $this->validationResultFactory->getWithErrors($errors);
    }

    private function createDataset(array $row, array $columnNames): array
    {
        $dataset = [];

        $undefinedColumnNameCounter = 0;
        foreach ($row as $index => $value) {
            if (isset($columnNames[$index])) {
                $columnName = $columnNames[$index];
            } else {
                $undefinedColumnNameCounter++;
                $columnName = 'undefined';
                if ($undefinedColumnNameCounter > 1) {
                    $columnName .= '_' . $undefinedColumnNameCounter;
                }
            }

            $dataset[$columnName] = $value;
        }

        return $dataset;
    }

    /**
     * @return string
     */
    public function getSeparator(): string
    {
        return $this->separator;
    }
}
