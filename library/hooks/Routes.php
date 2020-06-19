<?php

namespace Application\Hook;

use Application\Model\Pixel;

class Routes
{

    public function setClickId()
    {
        $pixel = new Pixel();
        $pixel->setClickID();
    }

}
