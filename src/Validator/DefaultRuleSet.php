<?php

namespace Basilicom\ImportDataValidator\Validator;

use Basilicom\ImportDataValidator\Rules\AbstractRule;

class DefaultRuleSet implements RuleSetInterface
{
    /** @var AbstractRule[] */
    private array $rules;

    /**
     * @param AbstractRule[] $rules
     */
    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
