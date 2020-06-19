<?php

namespace Application\Model;

use Application\Config;
use Application\Session;
use Exception;

class Configuration
{
    private $configId, $configData;

    private static $instance = null;

    public function __construct($configId = null)
    {
        if ($configId === null) {
            $this->configId = Session::get('steps.current.configId');
        } else {
            $this->configId = $configId;
        }
        $this->configData = Config::configurations(
            sprintf('%d', $this->configId)
        );
        if ($this->configData === null && !is_array($this->configData)) {
            if (DEV_MODE) {
                Session::set('lastException.message', sprintf(
                    'Configuration not found with id %d', $this->configId
                ));
            }

            $properError = sprintf(
                'Configuration not found with id %d.', $this->configId
            );

            Session::set('lastExceptionToPopup.message', $properError);
            throw new Exception('General config error.' . $properError, 1001);
        }
    }

    public function __call($methodName, $arguments)
    {

        $camelCaseKey = str_replace('get', '', $methodName);
        $snakeCaseKey = $this->camelCaseToSnakeCase($camelCaseKey);

        if (array_key_exists($snakeCaseKey, $this->configData)) {
            return $this->configData[$snakeCaseKey];
        }

        return false;
        // throw new Exception(
        //     sprintf(
        //         '%s::%s method not found!', get_called_class(), $methodName
        //     )
        // );
    }

    private function camelCaseToSnakeCase($camelCase)
    {
        $camelCase = lcfirst($camelCase);
        $lowerCase = strtolower($camelCase);
        $snakeCase = '';
        for ($i = 0, $len = strlen($camelCase); $i < $len; $i++) {
            if ($camelCase[$i] !== $lowerCase[$i]) {
                $snakeCase .= '_';
            }
            $snakeCase .= $lowerCase[$i];
        }
        return $snakeCase;
    }

    public function getCrm()
    {
        $crm = Config::crms(sprintf('%d', $this->configData['crm_id']));
        if ($crm === null && !is_array($crm)) {
            if (DEV_MODE) {
                Session::set('lastException.message', sprintf(
                    '(Configuration # %s) crm not found with id %d',
                    $this->configId, $this->configData['crm_id']
                ));
            }

            $properError = sprintf(
                '(Configuration # %s) crm not found with id %d',
                    $this->configId, $this->configData['crm_id']
            );

            Session::set('lastExceptionToPopup.message', $properError);
            throw new Exception('General config error.' . $properError, 1001);
        }
        return $crm;
    }

    public function getCrmType()
    {
        return Config::crms(
            sprintf('%d.crm_type', $this->configData['crm_id'])
        );
    }

}
