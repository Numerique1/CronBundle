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

class RunCronCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('cron:cronjob:run')
        ->setDescription("Run all CronJob based on \$nextRun <comment>(this command must be called by your system cron manager)</comment>")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        ini_set('memory_limit', -1); //remove memory limits to prevent crash when several jobs are runned;
        $cronJobs = $em->getRepository('Numerique1BundleCronBundle:CronJob')
            ->findBy([], ['nextRun'=>'ASC']);

        foreach($cronJobs as $job){
            $date = new \DateTime('now');
            if($job->getNextRun() <= $date && (null === $job->getLockUntil() || $job->getLockUntil() <= $date) ){
                if($job instanceof CronJob)
                {
                    $job->beforeRun();
                    $em->persist($job);
                    $em->flush();

                    $output = new BufferedOutput();
                    $command = $this->getApplication()->find($job->getCommand());
                    $command->run($input, $output);

                    $job->afterRun();
                    $em->persist($job);
                    $em->flush();
                    sleep(1);
                }
            }
        }

    }
}
