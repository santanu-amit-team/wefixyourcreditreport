<?php

namespace Application\Controller;

use Application\Model\Scraper;

class ExtraController
{

    public function __construct()
    {

    }

    public function trafficLoadBalancer()
    {
        $scrapper = new Scrapper();
        $scrapper->initialize();
    }

}
