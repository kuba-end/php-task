<?php

namespace App\Common\Infrastructure\Command;

use App\Common\Infrastructure\Factory\DepartmentFactory;
use App\Common\Infrastructure\Factory\EmployeeFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:seed',
    description: 'Add a short description for your command',
)]
class SeedCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // create 10 departments
        DepartmentFactory::createMany(7);
        EmployeeFactory::createMany(30);
//        DepartmentFactory::repository()->truncate(); // deletes all rows
//        EmployeeFactory::repository()->truncate(); // deletes all rows

        // flush DB writes
        return Command::SUCCESS;
    }
}
