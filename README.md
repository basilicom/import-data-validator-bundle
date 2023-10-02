# Import Data Validator Bundle

-------

## Installation
Require the bundle using
```
composer require basilicom/import-data-validator-bundle
```

## Configuration
Define validator, rules and ruleset via services.yaml

### Validator
* Implement `\Basilicom\ImportDataValidator\Validator\ValidatorInterface`
* or use the default implementations `\Basilicom\ImportDataValidator\Validator\DefaultCsvValidator` and `\Basilicom\ImportDataValidator\Validator\DefaultXlsxValidator`
```
// services.yaml

csv.validator.service:
  class: Basilicom\ImportDataValidator\Validator\DefaultCsvValidator
  public: true
  arguments:
    $separator: ';'

xlsx.validator.service:
  class: Basilicom\ImportDataValidator\Validator\DefaultXlsxValidator
  public: true
  arguments:
    $csvValidator: '@csv.validator.service'
    $datasheetName: 'Sheet 1'
```

### Rules
* Extend `\Basilicom\ImportDataValidator\Rules\AbstractRule`
* or use existing rules in `src/Rules`

```
// services.yaml

rules.dataset-count:
  class: Basilicom\ImportDataValidator\Rules\DatasetCountRule
  arguments:
    $countHeadline: false
    $min: 5
    $max: 10

rules.existing-columns:
  class: Basilicom\ImportDataValidator\Rules\ExistingColumnsRule
  arguments:
    $columnNames:
      - 'SKU'
      - 'Title'
      - 'Description'
      - 'Barcode'
      - 'Size'
      - 'Price'

rules.regex.sizes:
  class: Basilicom\ImportDataValidator\Rules\RegexRule
  arguments:
    $canBeEmpty: true
    $columnNames:
      - 'Size'
    $regex: '^(XS|S|M|L|XL|XXL)$'

rules.regex.sku:
  class: Basilicom\ImportDataValidator\Rules\RegexRule
  arguments:
    $canBeEmpty: false
    $columnNames:
      - 'SKU'
    $regex: '^\d{3}$'
```

### Ruleset
* Implement `\Basilicom\ImportDataValidator\Validator\RuleSetInterface`
* or use the default implementation `\Basilicom\ImportDataValidator\Validator\DefaultRuleSet`

```
// services.yaml

ruleset.service:
  class: Basilicom\ImportDataValidator\Validator\DefaultRuleSet
  public: true
  arguments:
    $rules:
      - '@rules.dataset-count'
      - '@rules.existing-columns'
      - '@rules.regex.sizes'
      - '@rules.regex.sku'
```


## Usage
Command:
```
bin/console basilicom:import-data-validator:validate ./file.xlsx xlsx.validator.service ruleset.service
```

Validator:
```
<?php

use Basilicom\ImportDataValidator\Validator\Result\Exception\ValidationErrorException;
use Basilicom\ImportDataValidator\Validator\RuleSetInterface;
use Basilicom\ImportDataValidator\Validator\ValidatorInterface;

class Import
{
    private ValidatorInterface $validator;
    private RuleSetInterface $ruleSet;

    public function __construct(
        ValidatorInterface $validator,
        RuleSetInterface $ruleSet
    ) {
        $this->validator = $validator;
        $this->ruleSet = $ruleSet;
    }

    /**
     * @throws ValidationErrorException
     */
    public function import(string $importFilePath)
    {
        $result = $this->validator->validate($importFilePath, $this->ruleSet);
        if (!$result->isValid()) {
            throw new ValidationErrorException(
                'Import file not valid. ' . count($result->getErrors()) . ' error(s).'
            );
        }
    }
}
```



