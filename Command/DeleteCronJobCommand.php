<?php

namespace Numerique1\Bundle\CronBundle\Command;

use Numerique1\Bundle\CronBundle\Entity\CronJob;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class DeleteCronJobCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('cron:cronjob:delete')
        ->setDefinition(array(
            new InputArgument('cmd', InputArgument::REQUIRED, 'The command'),
        ));

    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $input->getArgument('cmd');
//        $this->validateCommand($command);


        $em = $this->getContainer()->get('doctrine')->getManager();
        $job = $em->getRepository('Numerique1\Bundle\CronBundle\Entity\CronJob')->findOneBy(array("command" => $command));
        if(!$job instanceof CronJob){
            $output->writeln(sprintf('<error>No job found for command : "%s"</error>', $command));
            return;
        }

        $em->remove($job);
        $em->flush();
        $output->writeln(sprintf('<info>Job successfully deleted : "%s"</info>', $command));
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('cmd')) {
            $command = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please give the Command:',
                function($command) {
                    if (empty($command)) {
                        throw new \Exception('Command can not be empty');
                    }

                    return $command;
                }
            );
            $input->setArgument('cmd', $command);
        }
    }

//    public function validateCommand($command){
//        $parts = explode(' ', $command);
//        $this->getApplication()->get((string) $parts[0]);
//        return $command;
//    }
}