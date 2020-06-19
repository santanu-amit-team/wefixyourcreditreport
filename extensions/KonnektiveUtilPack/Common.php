<?php

namespace Extension\KonnektiveUtilPack;

use Application\Session;
use Application\Config;

class Common
{
    protected $currentStepId, $crmType;

    public function __construct()
    {
        $this->currentStepId    = (int) Session::get('steps.current.id');
        $this->previousStepId   = (int) Session::get('steps.previous.id');
        $this->currentConfigId  = (int) Session::get('steps.current.configId');
        $this->previousConfigId = (int) Session::get('steps.previous.configId');
        if (Session::get('steps.current.pageType') === 'thankyouPage') {
            $this->currentConfigId = $this->previousConfigId;
        }
        $this->previousConfigId = empty($this->previousConfigId)? $this->currentConfigId : $this->previousConfigId;
        $this->previousStepId = empty($this->previousStepId)? $this->currentStepId : $this->previousStepId;
        $this->convertIp = Config::extensionsConfig('KonnektiveUtilPack.convert_ip');
    }
    
    public function checkExtensionStatus($extensionName)
    {
        $extensions = Config::extensions();
        $isExtensionActive = false;
        if (!empty($extensions))
        {
            foreach ($extensions as $extension)
            {
                if ($extension['extension_slug'] == $extensionName)
                {
                    $isExtensionActive = $extension['active'];
                    break;
                }
            }
        }
        return $isExtensionActive;
    }
    
    public function convertIp($ip = null)
    {
        $ip6 = self::parseIp6($ip);
        $ip4 = ($ip6[6] >> 8) . '.' . ($ip6[6] & 0xff) . '.' . ($ip6[7] >> 8) . '.' . ($ip6[7] & 0xff);
        if (!filter_var($ip4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || $ip4 === "0.0.0.0")
        {
            return sprintf(
                    '%s.%s.%s.%s', rand(1, 255), rand(1, 255), rand(1, 255), rand(1, 255)
            );
        }
        return $ip4;
    }

    public function parseIp6($str)
    {
        for ($i = 0; $i < 8; $i++)
        {
            $ar[$i] = 0;
        }

        if ($str == "::")
        {
            return $ar;
        }

        $sar = explode(':', $str);
        $slen = count($sar);
        if ($slen > 8)
        {
            $slen = 8;
        }

        $j = 0;
        for ($i = 0; $i < $slen; $i++)
        {
            if ($i && $sar[$i] == "")
            {
                $j = 9 - $slen + $i;
                continue;
            }
            $ar[$j] = hexdec('0x' . $sar[$i]);
            $j++;
        }
        return $ar;
    }

}
