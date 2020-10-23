<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// This is a CLI (command line interface) command accessible by typing bin/console in a terminal
class ResetScoreCommand extends Command
{
    // Firdst we need to inject database management when object is created
    public function __construct(EntityManagerInterface $doctrine)
    {
        // also execute the construct method from nherited class : Symfony\Component\Console\Command\Command
        parent::__construct();
        $this->em      = $doctrine;
    }

    protected function configure()
    {
        $this
            // The name of the command, type bin/console in terminal, magic !
            // You can shortcut name : memo:res will execute the same command ;)
            ->setName('memory:reset-score')
            ->setHelp($this->getCommandHelp())
            ->setDescription('Delete all scores from memory table.')
    ;
    }

    // Before execute !
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        // TODO : add a confirmation message
        // A title
        $title      = 'Delete all scores from memory table.';
        // Boundary is based on nb of chars from $title var
        $boundaries = str_repeat('=', mb_strlen($title));
        // Same as echo with a carriage return in cli (ln = new line)
        $output->writeln($boundaries);
        $output->writeln($title);
        $output->writeln($boundaries);
    }

    // The main function, real logc goes here !
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('> Truncate memory table');
        // Send a basic query with no dql.
        $RAW_QUERY = 'DELETE FROM memory;';
        // PDO prepare style
        $statement = $this->em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $this->em->flush();
        $output->writeln('DONE !');
        // Exit with status code = 0 = Command executed without error
        exit(0);
    }

    // just return help message by typing bin/console memory:reset-score -h
    private function getCommandHelp()
    {
        return 'This will delete all entries in memory table.';
    }
}
