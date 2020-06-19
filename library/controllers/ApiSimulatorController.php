<?php

namespace Application\Controller;

use Exception;
use Application\Response;
use Application\Config;

class ApiSimulatorController
{

    public function __construct()
    {
        
    }

    public function gotoApiSimulator(...$p)
    {      
        try
        {
            if (!class_exists('\Extension\ApiSimulator\ApiSimulator') || !$this->checkExtensionStatus("ApiSimulator"))
                throw new Exception("Api Simulator not activated");
            $apiSimulatorObj = new \Extension\ApiSimulator\ApiSimulator;
            call_user_func_array(array($apiSimulatorObj, 'processCrmRequest'), array(
                empty($p) ? null : implode('/', $p)
                ));
        }
        catch (Exception $ex)
        {
            Response::$disableLog = true;
            return Response::send(
                            array(
                                'success' => false,
                                'msg' => $ex->getMessage()
                            )
            );
        }
    }

    private function checkExtensionStatus($extensionName)
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

}
