<?php

namespace Extension\CbUtilityPackage;

use Application\Config;
use Application\CrmPayload;
use Application\Request;

class ChangeIp extends Common
{
    public function __construct(){
        parent::__construct();
    }
    
    public function changeIp()
    {
        if(empty($this->convertIp)) {
            return;
        }
        
        $clientIp = Request::getClientIp();
        if(filter_var($clientIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && $this->convertIp) {
            $clientIp = $this->convertIp($clientIp);
        }
        
        CrmPayload::set('ipAddress', $clientIp);
    }

}
