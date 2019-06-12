<?php
namespace Numerique1\Bundle\CronBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCronJobCommand
 * @package Numerique1\Bundle\CronBundle\Command
 */
class ListCronJobCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('cron:cronjob:list')
        ->setDescription("List all existing CronJob")
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()
            ->get('doctrine')
            ->getManager();
        $cronJobs = $em->getRepository('Numerique1BundleCronBundle:CronJob')
            ->findBy([], ['nextRun'=>'ASC']);

        $rows = array();
        foreach ($cronJobs as $job)
        {
            $rows[] = array(
                $job->getCommand(),
                $job->getRunInterval(),
                $job->getRunAt(),
                $job->getNextRun(true),
                $job->getLastRun(true),
                $job->getLastSuccess(true)
            );
        }

        $table = new Table($output);
        $table->setHeaders(array(
            'Command',
            'Interval',
            'At',
            'Next',
            'Last',
            'Last-Success'
        ))
            ->setRows($rows);
        $table->render();
    }
}
