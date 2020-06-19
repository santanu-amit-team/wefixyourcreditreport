<?php

namespace Extension\CbUtilityPackage;

use Application\Session;
use Application\Config;
use Application\CrmPayload;
use Application\Http;
use Extension\CbUtilityPackage\Crmstructure;

class AdvancedDebug
{

    public function __construct()
    {
        
    }

    public function captureRequestParams()
    {
        $advancedDebug = Config::extensionsConfig(
                        'CbUtilityPackage.enable_advanced_debug'
        );

        if ($advancedDebug == false)
        {
            return;
        }

        $payload = Crmpayload::all();
        if (!DEV_MODE || empty($payload))
        {
            return;
        }

        $Crmstructure = new Crmstructure;
        $method = CrmPayload::get('meta.crmMethod');
        $crmType = CrmPayload::get('meta.crmType');
        $this->checkPayload = $Crmstructure->getStructure($crmType, $method);
        if (empty($this->checkPayload))
        {
            return;
        }
        $rawPayload = Http::getOptions();
        $this->crmPayload = !empty($rawPayload[10015]) ? json_decode($rawPayload[10015], true) : '';
        $this->exceptions['type'] = array();
        $this->exceptions['length'] = array();
        foreach ($this->crmPayload as $crmPayloadKey => $crmPayloadVal)
        {
            if (array_key_exists($crmPayloadKey, $this->checkPayload))
            {
                $this->getArrayItems($crmPayloadKey, $crmPayloadVal);
            }
        }

        if (!empty($this->exceptions))
        {
            Session::set('lastException.advancedDebug.' . $method, $this->exceptions);
        }
        
        //echo "here";die;
    }

    public function getArrayItems($crmPayloadKey, $crmPayloadVal)
    {
        if (!empty($crmPayloadVal))
        {
            if (is_array($crmPayloadVal))
            {
                foreach ($crmPayloadVal as $k => $v)
                {
                    $this->getArrayItems($k, $v);
                }
            }
            else
            {
                $check = $this->search($this->checkPayload, $crmPayloadKey);
                if (!empty($check))
                {
                    $func = ($check['type'] == 'ctype_digit') ? $check['type'] : "is_" . $check['type'];

                    if (!call_user_func_array($func, array($crmPayloadVal)))
                    {
                        array_push($this->exceptions['type'], $crmPayloadKey);
                    }
                    if (!empty($check['length']))
                    {
                        $varLength = strlen($crmPayloadVal);
                        if ($varLength > $check['length'])
                        {
                            array_push($this->exceptions['length'], $crmPayloadKey);
                        }
                    }
                }
            }
        }
    }

    public function search($array, $sKey)
    {
        foreach ($array as $key => $value)
        {
            if ($key == $sKey)
            {
                return $value;
            }
            elseif (is_array($value) && !isset($value['type']))
            {
                $res = $this->search($value, $sKey);
                if (!empty($res))
                {
                    return $res;
                }
            }
        }
        return false;
    }

}
