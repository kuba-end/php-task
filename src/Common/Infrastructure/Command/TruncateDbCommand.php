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
    name: 'app:db:truncate',
    description: 'Add a short description for your command',
)]
class TruncateDbCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        DepartmentFactory::repository()->truncate();
        EmployeeFactory::repository()->truncate();

        $io->success("Database population complete!");

        return Command::SUCCESS;
    }
}
