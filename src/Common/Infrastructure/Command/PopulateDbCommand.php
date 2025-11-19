<?php

namespace App\Common\Infrastructure\Command;

use App\Common\Infrastructure\Factory\DepartmentFactory;
use App\Common\Infrastructure\Factory\EmployeeFactory;
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

        $departments = $io->ask(
            'How many departments should be created?',
            5,
            function ($value) {
                if (!is_numeric($value) || $value < 1) {
                    throw new \RuntimeException('Please provide a positive integer.');
                }
                return (int) $value;
            }
        );

        $employees = $io->ask(
            'How many employees should be created?',
            30,
            function ($value) {
                if (!is_numeric($value) || $value < 1) {
                    throw new \RuntimeException('Please provide a positive integer.');
                }
                return (int) $value;
            }
        );

        $io->section("Creating $departments departments...");
        DepartmentFactory::createMany($departments);

        $io->section("Creating $employees employees...");
        EmployeeFactory::createMany($employees);

        $io->success("Database population complete!");

        return Command::SUCCESS;
    }
}
