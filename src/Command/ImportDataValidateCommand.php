<?php

namespace Basilicom\ImportDataValidator\Command;

use Basilicom\ImportDataValidator\Validator\Result\Exception\ValidationErrorException;
use Basilicom\ImportDataValidator\Validator\RuleSetInterface;
use Basilicom\ImportDataValidator\Validator\ValidatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImportDataValidateCommand extends Command
{
    const COMMAND               = 'basilicom:import-data-validator:validate';
    const FILEPATH_ARG          = 'filepath';
    const VALIDATOR_SERVICE_ARG = 'validator-service';
    const RULESET_SERVICE_ARG   = 'ruleset-service';

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::COMMAND)
            ->addArgument(
                self::FILEPATH_ARG,
                InputArgument::REQUIRED
            )
            ->addArgument(
                self::VALIDATOR_SERVICE_ARG,
                InputArgument::REQUIRED
            )
            ->addArgument(
                self::RULESET_SERVICE_ARG,
                InputArgument::REQUIRED
            );
    }

    /**
     * @throws ValidationErrorException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filepath         = $input->getArgument(self::FILEPATH_ARG);
        $validatorService = $input->getArgument(self::VALIDATOR_SERVICE_ARG);
        $rulesetService   = $input->getArgument(self::RULESET_SERVICE_ARG);

        if (!file_exists($filepath)) {
            $output->writeln('<error>File ' . $filepath . ' does not exist.</error>');

            return Command::FAILURE;
        }

        if (!$this->container->has($validatorService)) {
            $output->writeln('<error>The service "' . $validatorService . '" does not exist in the container.</error>');

            return Command::FAILURE;
        }

        $validator = $this->container->get($validatorService);
        if (!($validator instanceof ValidatorInterface)) {
            $output->writeln('<error>The service "' . $validatorService . '" does not implement "' . ValidatorInterface::class . '".</error>');

            return Command::FAILURE;
        }

        if (!$this->container->has($rulesetService)) {
            $output->writeln('<error>The service "' . $rulesetService . '" does not exist in the container.</error>');

            return Command::FAILURE;
        }

        $ruleSet = $this->container->get($rulesetService);
        if (!($ruleSet instanceof RuleSetInterface)) {
            $output->writeln('<error>The service "' . $rulesetService . '" does not implement "' . RuleSetInterface::class . '".</error>');

            return Command::FAILURE;
        }

        $result = $validator->validate($filepath, $ruleSet);
        $output->writeln('<info>Test ' . basename($filepath) . ':</info>');
        $output->writeln('Valid: ' . json_encode($result->isValid()));
        $output->writeln('Errors (' . count($result->getErrors()) . '):');
        foreach ($result->getErrors() as $error) {
            $description = [];
            if ($error->getLineNumber() !== null) {
                $description[] = 'L' . $error->getLineNumber();
            }
            $description[] = $error->getInfo();

            $output->writeln(implode(', ', $description));
        }

        return $result->isValid() ? Command::SUCCESS : Command::INVALID;
    }
}
