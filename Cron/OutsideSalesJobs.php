<?php

namespace Nati\OutsideSales\Cron;

use Nati\OutsideSales\Console\Command\OutsideQueue;

class OutsideSalesJobs
{
    protected $command;

    public function __construct(OutsideQueue $command)
    {
        $this->command = $command;
    }

    public function execute()
    {
        $this->command->executeCron();
    }
}