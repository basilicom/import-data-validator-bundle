<?php

namespace Basilicom\ImportDataValidator\Validator;

use Basilicom\ImportDataValidator\Rules\Error\FileError;
use Basilicom\ImportDataValidator\Validator\Result\Exception\ValidationErrorException;
use Basilicom\ImportDataValidator\Validator\Result\ValidationErrorFactory;
use Basilicom\ImportDataValidator\Validator\Result\ValidationResultFactory;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DefaultXlsxValidator implements ValidatorInterface
{
    private ValidationResultFactory $validationResultFactory;
    private ValidationErrorFactory $validationErrorFactory;
    private DefaultCsvValidator $csvValidator;
    private ?string $datasheetName;

    public function __construct(
        ValidationResultFactory $validationResultFactory,
        ValidationErrorFactory  $validationErrorFactory,
        DefaultCsvValidator     $csvValidator,
        ?string                 $datasheetName = null
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->validationErrorFactory  = $validationErrorFactory;
        $this->csvValidator            = $csvValidator;
        $this->datasheetName           = $datasheetName;
    }

    /**
     * @throws ValidationErrorException
     */
    public function validate(string $filepath, RuleSetInterface $ruleSet): Result\ValidationResult
    {
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

        fclose($handle);

        $spreadsheet = IOFactory::load($filepath);
        $worksheet = !empty($this->datasheetName) ? $spreadsheet->getSheetByName($this->datasheetName) : $spreadsheet->getActiveSheet();

        if (empty($worksheet)) {
            return $this->validationResultFactory->getWithErrors(
                [
                    $this->validationErrorFactory->get(
                        FileError::class,
                        null,
                        'Could not find sheet with name ' . $this->datasheetName . ' in file ' . $filepath
                    )
                ]
            );
        }

        $tempCsvFile = tempnam(sys_get_temp_dir(), 'csv_');
        unlink($tempCsvFile);
        $tempCsvFile .= '.csv';

        $fp = fopen($tempCsvFile, 'w');

        foreach ($worksheet->getRowIterator() as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getValue();
            }
            fputcsv($fp, $rowData, $this->csvValidator->getSeparator());
        }

        fclose($fp);

        $result = $this->csvValidator->validate($tempCsvFile, $ruleSet);

        unlink($tempCsvFile);

        return $result;
    }
}
