<?php

namespace Basilicom\ImportDataValidator\Rules;

use Basilicom\ImportDataValidator\Rules\Error\DatasetCountError;
use Basilicom\ImportDataValidator\Validator\Result\Exception\ValidationErrorException;
use Basilicom\ImportDataValidator\Validator\Result\ValidationErrorFactory;

class DatasetCountRule extends AbstractRule
{
    private ?int $min;
    private ?int $max;
    private bool $countHeadline;
    private int $headerLineSize;

    public function __construct(
        ValidationErrorFactory $validationErrorFactory,
        ?int $min = null,
        ?int $max = null,
        $countHeadline = false,
        int $headerLineSize = 1
    ) {
        parent::__construct($validationErrorFactory);

        $this->min            = $min;
        $this->max            = $max;
        $this->countHeadline  = $countHeadline;
        $this->headerLineSize = $headerLineSize;
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
     * @param string $filepath
     * @param array $options
     * @return array
     * @throws ValidationErrorException
     */
    public function validateFile(string $filepath, array $options = []): array
    {
        // https://www.myintervals.com/blog/2011/06/21/calculate-the-number-of-lines-in-a-csv-file-using-php-and-linux/
        $linecount = intval(exec('perl -pe \'s/\r\n|\n|\r/\n/g\' ' . escapeshellarg($filepath) . ' | wc -l'));
        if (!$this->countHeadline) {
            $linecount -= $this->headerLineSize;
        }

        $errors = [];

        if (($this->min !== null && $linecount < $this->min) || ($this->max !== null && $linecount > $this->max)) {
            $info = [];
            if ($this->min !== null) {
                $info[] = 'min ' . $this->min;
            }
            if ($this->max !== null) {
                $info[] = 'max ' . $this->max;
            }

            $errors[] = $this->validationErrorFactory->get(
                DatasetCountError::class,
                null,
                'File has ' . $linecount . ' datasets. Expected ' . implode(', ', $info)
            );
        }

        return $errors;
    }
}
