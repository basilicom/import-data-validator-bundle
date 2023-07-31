<?php

namespace Basilicom\ImportDataValidator\Validator;

use Basilicom\ImportDataValidator\Validator\Result\Exception\ValidationErrorException;
use Basilicom\ImportDataValidator\Validator\Result\ValidationResult;

interface ValidatorInterface
{
    /**
     * @throws ValidationErrorException
     */
    public function validate(string $filepath, RuleSetInterface $ruleSet): ValidationResult;
}
