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

class CreateCronJobCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('cron:cronjob:create');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        #COMMAND
        $output->writeln('');
        $output->writeln('<info>The command to execute. You may add extra arguments.</info>');
        $question = new Question('<question>Command:</question> ', false);
        $command = $this->getHelperSet()->get('question')->ask($input, $output, $question);
        $this->validateCommand($command);
        #INTERVAL
        $output->writeln('');
        $output->writeln('<info>Execution interval (PHP DateInterval syntax).</info>');
        $question = new Question('<question>Interval:</question> ', false);
        $interval = $this->getHelperSet()->get('question')->ask($input, $output, $question);
        $this->validateInterval($interval);

        #NEXTRUN
        $output->writeln('');
        $output->writeln('<info>Next time the command will be run (format : "d/m/Y H:i:s").</info>');
        $question = new Question('<question>Next run:</question> ', false);
        $date = $this->getHelperSet()->get('question')->ask($input, $output, $question);
        $this->validateDate($date);

        #LOCK
        $output->writeln('');
        $output->writeln('<info>Lock interval (PHP DateInterval syntax) (default : "PT5M").</info>');
        $question = new Question('<question>Lock interval:</question> ', false);
        $lockInterval = $this->getHelperSet()->get('question')->ask($input, $output, $question);
        $output->writeln('');

        #JOB CREATION
        $job = new CronJob($command, $interval, $date);
        if($lockInterval){
            $this->validateInterval($lockInterval);
            $job->setLockInterval($lockInterval);
        }
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->persist($job);
        $em->flush();
        $output->writeln(sprintf('<info>Succesfully created : "%s"</info>', $job->getCommand()));
    }

    public function validateCommand($command){
        $parts = explode(' ', $command);
        $this->getApplication()->get((string) $parts[0]);
        return $command;
    }

    public function validateInterval($interval){
        $interval = new \DateInterval($interval);
        return $interval;
    }

    public function validateDate($date){
        $date = \DateTime::createFromFormat("d/m/Y H:i:s", $date);
        if(!($date instanceof \DateTime)){
            throw new \Exception(sprintf('Bad format for date %s', $date));
        }
    }
}