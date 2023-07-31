<?php

namespace Basilicom\ImportDataValidator\Validator;

use Basilicom\ImportDataValidator\Rules\AbstractRule;

interface RuleSetInterface
{
    /**
     * @return AbstractRule[]
     */
    public function getRules(): array;
}
