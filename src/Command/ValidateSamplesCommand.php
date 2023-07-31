<?php

namespace Basilicom\ImportDataValidator\Command;

use Basilicom\ImportDataValidator\Validator\Result\ValidationResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateSamplesCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('basilicom:import-data-validator:test-samples');
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $samplePath = __DIR__ . '/../Resources/sample';

        $samples = [
            'invalid',
            'valid'
        ];

        $commandName = ImportDataValidateCommand::COMMAND;

        /** @var ValidationResult $result */
        foreach ($samples as $filename) {
            foreach ([
                '.csv' => 'sample.validator.csv',
                '.xlsx' => 'sample.validator.xlsx'
             ] as $extension => $validatorService) {
                $arguments = [
                    ImportDataValidateCommand::FILEPATH_ARG          => $samplePath . '/' . $filename . $extension,
                    ImportDataValidateCommand::VALIDATOR_SERVICE_ARG => $validatorService,
                    ImportDataValidateCommand::RULESET_SERVICE_ARG   => 'sample.ruleset'
                ];

                $commandInput = new ArrayInput($arguments);
                $returnCode = $this->getApplication()->find($commandName)->run($commandInput, $output);

                if ($returnCode === Command::FAILURE) {
                    return $returnCode;
                }
            }
        }

        return Command::SUCCESS;
    }
}
