<?php

namespace Nati\OutsideSales\Cron;

use Nati\OutsideSales\Services\Bling;

class OutsideTokensJobs
{
    protected $bling;

    public function __construct(Bling $bling)
    {
        $this->bling = $bling;
    }

    public function execute()
    {
        $this->bling->getBlingAccessToken();
    }
}