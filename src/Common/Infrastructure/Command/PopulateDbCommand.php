<?php

namespace App\Common\Infrastructure\Command;

use App\Common\Infrastructure\FixtureFactory\DepartmentFactory;
use App\Common\Infrastructure\FixtureFactory\EmployeeFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:db:populate',
    description: 'Populate database with fake departments and employees',
)]
class PopulateDbCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Database Population Wizard');

        /** @var string $departments */
        $departments = $io->ask(
            'How many departments should be created?',
            '5',
            function ($value) {
                if (!is_numeric($value) || $value < 1) {
                    throw new \RuntimeException('Please provide a positive integer.');
                }

                return (int) $value;
            }
        );

        /** @var string $employees */
        $employees = $io->ask(
            'How many employees should be created?',
            '30',
            function ($value) {
                if (!is_numeric($value) || $value < 1) {
                    throw new \RuntimeException('Please provide a positive integer.');
                }

                return (int) $value;
            }
        );

        $io->section(sprintf('Creating %s departments...', $departments));
        DepartmentFactory::createMany((int) $departments);

        $io->section(sprintf('Creating %s employees...', $employees));
        EmployeeFactory::createMany((int) $employees);

        $io->success('Database population complete!');

        return Command::SUCCESS;
    }
}
