<?php

namespace Lib\Console;

use Lib\Database\Migrator;
use Symfony\Component\Console\Input\InputInterface as Input;
use Symfony\Component\Console\Input\InputOption as Option;
use Symfony\Component\Console\Output\OutputInterface as Output;

class MigrateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('migrate')
            ->setDescription('Migrate database schema')
            ->addOption('down', 'd', Option::VALUE_NONE, 'Rollback database schema')
            ->addOption('steps', 's', Option::VALUE_REQUIRED, 'Number of steps to migrate');

        parent::configure();

        Migrator::boot();
    }

    protected function execute(Input $input, Output $output)
    {
        $migrations = Migrator::run([
            'down' => $input->getOption('down'),
            'steps' => $input->getOption('steps'),
        ]);
        foreach ($migrations as $migration) {
            $output->writeln('Migrated ' . get_class($migration));
        }

        return 0;
    }
}
