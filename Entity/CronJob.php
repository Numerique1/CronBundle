<?php

namespace Numerique1\Bundle\CronBundle\Entity;

/**
 * CronJob
 */
class CronJob
{
    /**
     * @var int
     */
    private $id;

    /**
     * The command name
     * @var string
     */
    private $command;

    /**
     * Task execution interval
     * @var string
     */
    private $runInterval;

    /**
     * Time at which the task must be executed
     * @var string
     */
    private $runAt = "00:00:00";

    /**
     * Next time the task must be executed
     * @var \DateTime
     */
    private $nextRun;

    /**
     * @var \DateTime
     */
    private $lastRun;

    /**
     * @var  \DateTime
     */
    private $lastSuccess;

    /**
     * @var string
     */
    private $lockInterval = "PT5M";

    /**
     * @var  \DateTime
     */
    private $lockUntil;

    /**
     * CronJob constructor.
     * @param $commandName
     * @param \DateTime $nextRun
     * @param string $frequencyRun
     * @param string $timeToLocked
     */
    public function __construct($command, $runInterval, $nextRun = null)
    {
        $this->command = $command;
        $this->runInterval = $runInterval;
        $this->setNextRun($nextRun);
        $this->setRunAt(explode(" ", $nextRun)[1]);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param mixed $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * @return string
     */
    public function getRunInterval()
    {
        return $this->runInterval;
    }

    /**
     * @param string $runInterval
     */
    public function setRunInterval($runInterval)
    {
        $this->runInterval = $runInterval;
    }

    /**
     * @return string
     */
    public function getRunAt()
    {
        return $this->runAt;
    }

    /**
     * @param string $runAt
     */
    public function setRunAt($runAt)
    {
        $this->runAt = $runAt;
    }

    /**
     * @return \DateTime
     */
    public function getLastRun($formatted = false)
    {
        return $formatted && $this->lastRun ? $this->lastRun->format('d/m/Y H:i:s') : $this->lastRun;
    }

    /**
     * @param \DateTime $lastRun
     */
    public function setLastRun($lastRun)
    {
        $this->lastRun = $lastRun;
    }

    /**
     * @return \DateTime
     */
    public function getLastSuccess($formatted = false)
    {
        return $formatted && $this->lastSuccess ? $this->lastSuccess->format('d/m/Y H:i:s') : $this->lastSuccess;
    }

    /**
     * @param \DateTime $lastSuccess
     */
    public function setLastSuccess($lastSuccess)
    {
        $this->lastSuccess = $lastSuccess;
    }

    /**
     * @return string
     */
    public function getNextRun($formatted = false)
    {
        return $formatted ? $this->nextRun->format('d/m/Y H:i:s') : $this->nextRun;
    }
    
    /**
     * Force nextRun date, used to intialize the first nextRun
     * @return \DateTime
     */
    public function setNextRun($date){
        $date = \DateTime::createFromFormat("d/m/Y H:i:s", $date);
        $this->nextRun = $date;
    }

    /**
     * @return mixed
     */
    public function getLockInterval()
    {
        return $this->lockInterval;
    }

    /**
     * @param mixed $lockInterval
     */
    public function setLockInterval($lockInterval)
    {
        $this->lockInterval = $lockInterval;
    }

    /**
     * @return \DateTime
     */
    public function getLockUntil()
    {
        return $this->lockUntil;
    }

    /**
     * @param \DateTime $lockUntil
     */
    public function setLockUntil()
    {
        $date = clone $this->nextRun;
        $date->add(new \DateInterval($this->lockInterval));
        $this->lockUntil = $date;
    }

    /**
     * This function must be called before the command run
     * calculates lockUntil and set lastRun
     */
    public function beforeRun(){
        $this->setLockUntil();
        $this->setLastRun($this->lastRun ? new \DateTime() : clone $this->nextRun);
    }

    /**
     * This function must be called after the command run
     * calculates nextRun and set lastSuccess
     */
    public function afterRun(){
        $date = clone $this->lastRun;

        //If interval is not in time, we will use runAt property to prevent the execution from shifting over time.
        if(strpos($this->runInterval,'PT') === false){
            list($h, $m, $s) = explode(":", $this->runAt);
            $date->setTime($h, $m, $s);
        }
        else { //Taking current hour and add interval until its greater than current time (minutes)
            list($h, $m, $s) = explode(":", $this->runAt);
            $date = new \DateTime();
            $date->setTime($h, $m, 0); // Current hour, 0 minute, [0 second]
            $now = new \DateTime();
            $now->sub(new \DateInterval($this->runInterval));
            while($date < $now){
                $date->add(new \DateInterval($this->runInterval));
            }
        }
        $date->add(new \DateInterval($this->runInterval));
        $this->nextRun = $date;
        $this->setLastSuccess(new \DateTime());
    }

}